<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class UiTranslationController extends Controller
{
    private const MAX_ITEMS = 180;
    private const MAX_TEXT_LENGTH = 500;
    private const CACHE_SECONDS = 604800; // 7 days
    private const PARALLEL_REQUESTS = 12;
    private const JOIN_SEPARATOR = "\n__PMSEP__\n";
    private const JOIN_MAX_ITEMS = 28;
    private const JOIN_MAX_CHARS = 2400;

    public function translate(Request $request)
    {
        $data = $request->validate([
            'target' => ['required', 'string', 'max:16', 'regex:/^[a-z]{2,3}(-[a-z]{2,3})?$/i'],
            'texts' => ['required', 'array', 'min:1', 'max:' . self::MAX_ITEMS],
            'texts.*' => ['nullable', 'string', 'max:' . self::MAX_TEXT_LENGTH],
        ]);

        $target = strtolower(trim($data['target']));
        $texts = array_map(static function ($item): string {
            return trim((string) $item);
        }, $data['texts']);

        if ($target === 'en') {
            return response()->json([
                'success' => true,
                'data' => [
                    'target' => $target,
                    'translations' => $texts,
                ],
            ]);
        }

        $translations = $texts;
        $missingByIndex = [];

        foreach ($texts as $index => $text) {
            if ($text === '' || !$this->containsLetters($text)) {
                continue;
            }

            $cacheKey = $this->cacheKey($target, $text);
            $cached = Cache::get($cacheKey);
            if (is_string($cached) && $cached !== '') {
                $translations[$index] = $cached;
                continue;
            }

            $missingByIndex[$index] = $text;
        }

        if ($missingByIndex !== []) {
            $resolvedByIndex = $this->translateManyInParallel($missingByIndex, $target);

            foreach ($missingByIndex as $index => $originalText) {
                $translated = trim((string) ($resolvedByIndex[$index] ?? ''));
                if ($translated === '') {
                    $translated = $originalText;
                }

                $translations[$index] = $translated;
                Cache::put($this->cacheKey($target, $originalText), $translated, self::CACHE_SECONDS);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'target' => $target,
                'translations' => $translations,
            ],
        ]);
    }

    private function containsLetters(string $text): bool
    {
        return (bool) preg_match('/\p{L}/u', $text);
    }

    private function cacheKey(string $target, string $text): string
    {
        return 'ui_translate:' . $target . ':' . md5($text);
    }

    private function translateManyInParallel(array $textsByIndex, string $target): array
    {
        $results = [];
        $groups = $this->buildJoinedGroups($textsByIndex);
        $joinedQueries = [];

        foreach ($groups as $groupKey => $chunk) {
            if (count($chunk) > 1) {
                $joinedQueries[$groupKey] = implode(self::JOIN_SEPARATOR, array_values($chunk));
            }
        }

        $joinedResponses = $joinedQueries !== []
            ? $this->requestJoinedChunks($joinedQueries, $target, false)
            : [];

        foreach ($groups as $groupKey => $chunk) {
            if (count($chunk) === 1) {
                $index = array_key_first($chunk);
                $source = (string) $chunk[$index];
                $translated = $this->translateText($source, $target);
                $results[$index] = $translated ?: $source;
                continue;
            }

            $joinedResponse = $joinedResponses[(string) $groupKey] ?? null;
            $joinedTranslated = $this->extractTranslationFromResponse($joinedResponse);
            if ($joinedTranslated) {
                $joined = $this->splitJoinedTranslation($joinedTranslated, $chunk);
                if ($joined !== null) {
                    foreach ($joined as $index => $translated) {
                        $results[$index] = $translated;
                    }
                    continue;
                }
            }

            $responses = $this->requestChunk($chunk, $target, false);

            foreach ($chunk as $index => $text) {
                $response = $responses[(string) $index] ?? null;
                $translated = $this->extractTranslationFromResponse($response);
                $results[$index] = $translated ?: $text;
            }
        }

        return $results;
    }

    private function buildJoinedGroups(array $textsByIndex): array
    {
        $groups = [];
        $current = [];
        $currentChars = 0;

        foreach ($textsByIndex as $index => $text) {
            $textChars = $this->stringLength($text);
            $nextChars = $currentChars + ($current === [] ? 0 : $this->stringLength(self::JOIN_SEPARATOR)) + $textChars;

            if (
                $current !== [] &&
                (count($current) >= self::JOIN_MAX_ITEMS || $nextChars > self::JOIN_MAX_CHARS)
            ) {
                $groups[] = $current;
                $current = [];
                $currentChars = 0;
            }

            if ($current !== []) {
                $currentChars += $this->stringLength(self::JOIN_SEPARATOR);
            }
            $current[$index] = $text;
            $currentChars += $textChars;
        }

        if ($current !== []) {
            $groups[] = $current;
        }

        return $groups;
    }

    private function requestJoinedChunks(array $joinedQueries, string $target, bool $skipSslVerify): array
    {
        try {
            return Http::pool(function (Pool $pool) use ($joinedQueries, $target, $skipSslVerify) {
                $requests = [];

                foreach ($joinedQueries as $groupKey => $joinedText) {
                    $request = $pool->as((string) $groupKey);

                    if ($skipSslVerify) {
                        $request = $request->withoutVerifying();
                    }

                    $requests[] = $request
                        ->timeout(8)
                        ->retry(1, 200)
                        ->get('https://translate.googleapis.com/translate_a/single', $this->buildQuery($joinedText, $target));
                }

                return $requests;
            });
        } catch (ConnectionException $exception) {
            $message = strtolower($exception->getMessage());
            if (!$skipSslVerify && str_contains($message, 'ssl certificate')) {
                return $this->requestJoinedChunks($joinedQueries, $target, true);
            }

            return [];
        } catch (\Throwable $exception) {
            return [];
        }
    }

    private function splitJoinedTranslation(string $joinedTranslated, array $chunk): ?array
    {
        $parts = explode(self::JOIN_SEPARATOR, $joinedTranslated);
        if (count($parts) !== count($chunk)) {
            $fallbackParts = preg_split('/\s*__PMSEP__\s*/u', $joinedTranslated) ?: [];
            if (count($fallbackParts) !== count($chunk)) {
                return null;
            }
            $parts = $fallbackParts;
        }

        $mapped = [];
        $keys = array_keys($chunk);
        foreach ($keys as $offset => $index) {
            $value = trim((string) ($parts[$offset] ?? ''));
            $mapped[$index] = $value !== '' ? $value : (string) $chunk[$index];
        }

        return $mapped;
    }

    private function stringLength(string $text): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($text, 'UTF-8');
        }

        return strlen($text);
    }

    private function requestChunk(array $chunk, string $target, bool $skipSslVerify): array
    {
        try {
            return Http::pool(function (Pool $pool) use ($chunk, $target, $skipSslVerify) {
                $requests = [];

                foreach ($chunk as $index => $text) {
                    $request = $pool->as((string) $index);

                    if ($skipSslVerify) {
                        $request = $request->withoutVerifying();
                    }

                    $requests[] = $request
                        ->timeout(8)
                        ->retry(1, 200)
                        ->get('https://translate.googleapis.com/translate_a/single', $this->buildQuery($text, $target));
                }

                return $requests;
            });
        } catch (ConnectionException $exception) {
            $message = strtolower($exception->getMessage());
            if (!$skipSslVerify && str_contains($message, 'ssl certificate')) {
                return $this->requestChunk($chunk, $target, true);
            }

            return [];
        } catch (\Throwable $exception) {
            return [];
        }
    }

    private function buildQuery(string $text, string $target): array
    {
        return [
            'client' => 'gtx',
            'sl' => 'auto',
            'tl' => $target,
            'dt' => 't',
            'q' => $text,
        ];
    }

    private function translateText(string $text, string $target): ?string
    {
        try {
            $response = Http::timeout(8)
                ->retry(1, 200)
                ->get('https://translate.googleapis.com/translate_a/single', $this->buildQuery($text, $target));
        } catch (ConnectionException $exception) {
            $message = strtolower($exception->getMessage());
            if (!str_contains($message, 'ssl certificate')) {
                return null;
            }

            try {
                $response = Http::withoutVerifying()
                    ->timeout(8)
                    ->retry(1, 200)
                    ->get('https://translate.googleapis.com/translate_a/single', $this->buildQuery($text, $target));
            } catch (\Throwable $exception) {
                return null;
            }
        } catch (\Throwable $exception) {
            return null;
        }

        return $this->extractTranslationFromResponse($response);
    }

    private function extractTranslationFromResponse(mixed $response): ?string
    {
        if (!$response instanceof Response || !$response->successful()) {
            return null;
        }

        try {
            $payload = $response->json();
            $rows = $payload[0] ?? null;
            if (!is_array($rows)) {
                return null;
            }

            $chunks = [];
            foreach ($rows as $row) {
                if (is_array($row) && isset($row[0])) {
                    $chunks[] = (string) $row[0];
                }
            }

            $translated = trim(implode('', $chunks));

            return $translated !== '' ? $translated : null;
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
