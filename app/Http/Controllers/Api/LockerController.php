<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Locker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class LockerController extends Controller
{
    private const LOCKER_TYPES = [
        Locker::TYPE_FACILITY_OWNED,
        Locker::TYPE_THIRD_PARTY_OWNED,
    ];

    private const SIZE_ORDER = [
        'small' => 1,
        'medium' => 2,
        'large' => 3,
        'xl' => 4,
    ];

    public function index(Request $request)
    {
        $includeCompartments = $request->boolean('include_compartments');
        $query = Locker::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('updated_at');

        $this->applyAggregates($query);
        if ($includeCompartments) {
            $this->applyCompartmentEagerLoad($query);
        }

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', "%{$term}%")
                    ->orWhere('locker_name', 'like', "%{$term}%")
                    ->orWhere('facility_name', 'like', "%{$term}%")
                    ->orWhere('location', 'like', "%{$term}%");
            });
        }
        if ($request->filled('code')) {
            $query->where('code', 'like', '%' . $request->string('code')->toString() . '%');
        }
        if ($request->filled('locker_name')) {
            $query->where('locker_name', 'like', '%' . $request->string('locker_name')->toString() . '%');
        }
        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->string('location')->toString() . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('facility_name')) {
            $query->where('facility_name', 'like', '%' . $request->string('facility_name')->toString() . '%');
        }
        if ($request->filled('locker_type')) {
            $query->where('locker_type', $request->string('locker_type')->toString());
        } elseif ($request->filled('type')) {
            $query->where('locker_type', $request->string('type')->toString());
        }

        $lockers = $query->get()->map(fn (Locker $locker) => $this->transformLocker($locker, $includeCompartments));

        return response()->json([
            'success' => true,
            'data' => $lockers,
        ]);
    }

    public function show(Request $request, Locker $locker)
    {
        if ($response = $this->denyUnlessOwns($request, $locker)) {
            return $response;
        }

        $query = Locker::query()->whereKey($locker->id);
        $this->applyAggregates($query);
        $this->applyCompartmentEagerLoad($query);
        $record = $query->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $this->transformLocker($record, true),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedPayload($request);
        $compartments = $this->normalizeCompartments($data['compartments'] ?? []);
        unset($data['compartments']);

        $data['locker_name'] = $data['locker_name'] ?? $data['code'];
        $data['locker_type'] = $data['locker_type'] ?? Locker::TYPE_FACILITY_OWNED;
        $data['capacity'] = !empty($compartments)
            ? array_sum(array_column($compartments, 'quantity'))
            : (int) ($data['capacity'] ?? 0);

        $data['user_id'] = $request->user()->id;
        $locker = DB::transaction(function () use ($data, $compartments, $request) {
            $locker = Locker::create($data);
            $this->syncCompartments($locker, $compartments, $request->user()->id);
            return $locker;
        });

        $query = Locker::query()->whereKey($locker->id);
        $this->applyAggregates($query);
        $this->applyCompartmentEagerLoad($query);
        $record = $query->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $this->transformLocker($record, true),
        ], 201);
    }

    public function update(Request $request, Locker $locker)
    {
        if ($response = $this->denyUnlessOwns($request, $locker)) {
            return $response;
        }

        $data = $this->validatedPayload($request, $locker);
        $compartments = $this->normalizeCompartments($data['compartments'] ?? []);
        $hasCompartments = array_key_exists('compartments', $data);
        unset($data['compartments']);

        if (empty($data['locker_name'])) {
            $data['locker_name'] = $data['code'] ?? $locker->code;
        }
        if (empty($data['locker_type'])) {
            $data['locker_type'] = $locker->locker_type ?: Locker::TYPE_FACILITY_OWNED;
        }
        if ($hasCompartments) {
            $data['capacity'] = array_sum(array_column($compartments, 'quantity'));
        } elseif (array_key_exists('capacity', $data)) {
            $data['capacity'] = (int) ($data['capacity'] ?? 0);
        }

        DB::transaction(function () use ($locker, $data, $hasCompartments, $compartments) {
            $locker->update($data);
            if ($hasCompartments) {
                $this->syncCompartments($locker, $compartments, $locker->user_id);
            }
        });

        $query = Locker::query()->whereKey($locker->id);
        $this->applyAggregates($query);
        $this->applyCompartmentEagerLoad($query);
        $record = $query->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $this->transformLocker($record, true),
        ]);
    }

    public function destroy(Request $request, Locker $locker)
    {
        if ($response = $this->denyUnlessOwns($request, $locker)) {
            return $response;
        }

        $locker->delete();

        return response()->json(['success' => true]);
    }

    private function validatedPayload(Request $request, ?Locker $locker = null): array
    {
        $rule = Rule::unique('lockers', 'code')->where('user_id', $request->user()->id);
        if ($locker) {
            $rule = $rule->ignore($locker->id);
        }

        return $request->validate([
            'code' => ['required', 'string', 'max:60', $rule],
            'locker_name' => ['nullable', 'string', 'max:120'],
            'locker_type' => ['nullable', Rule::in(self::LOCKER_TYPES)],
            'facility_name' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,maintenance,inactive'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'compartments' => ['sometimes', 'array'],
            'compartments.*.size_category' => ['required', Rule::in(array_keys(self::SIZE_ORDER))],
            'compartments.*.compartment_code' => ['required', 'string', 'max:40'],
            'compartments.*.quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'compartments.*.activation_status' => ['nullable', 'in:active,inactive'],
            'compartments.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:50000'],
        ]);
    }

    private function normalizeCompartments(array $rows): array
    {
        $normalized = [];
        $seen = [];

        foreach ($rows as $index => $row) {
            $size = strtolower(trim((string) ($row['size_category'] ?? '')));
            $code = trim((string) ($row['compartment_code'] ?? ''));
            $activationStatus = strtolower(trim((string) ($row['activation_status'] ?? 'active')));
            $quantity = (int) ($row['quantity'] ?? 0);

            if ($size === '' || $code === '' || $quantity < 1) {
                continue;
            }

            $key = $size . '|' . strtolower($code);
            if (isset($seen[$key])) {
                throw ValidationException::withMessages([
                    "compartments.{$index}.compartment_code" => "Duplicate compartment code '{$code}' in {$size} category.",
                ]);
            }
            $seen[$key] = true;

            $normalized[] = [
                'size_category' => $size,
                'compartment_code' => $code,
                'quantity' => $quantity,
                'activation_status' => $activationStatus === 'inactive' ? 'inactive' : 'active',
                'sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : count($normalized),
            ];
        }

        return $normalized;
    }

    private function syncCompartments(Locker $locker, array $rows, int $userId): void
    {
        $locker->compartments()->delete();
        if (empty($rows)) {
            return;
        }

        $payload = [];
        $now = now();
        foreach ($rows as $row) {
            $payload[] = [
                'user_id' => $userId,
                'locker_id' => $locker->id,
                'size_category' => $row['size_category'],
                'compartment_code' => $row['compartment_code'],
                'quantity' => $row['quantity'],
                'activation_status' => $row['activation_status'] ?? 'active',
                'sort_order' => $row['sort_order'] ?? 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $locker->compartments()->insert($payload);
    }

    private function applyAggregates(Builder $query): void
    {
        $query->withSum([
            'compartments as small_total' => fn ($q) => $q
                ->where('size_category', 'small')
                ->where('activation_status', 'active'),
        ], 'quantity');

        $query->withSum([
            'compartments as medium_total' => fn ($q) => $q
                ->where('size_category', 'medium')
                ->where('activation_status', 'active'),
        ], 'quantity');

        $query->withSum([
            'compartments as large_total' => fn ($q) => $q
                ->where('size_category', 'large')
                ->where('activation_status', 'active'),
        ], 'quantity');

        $query->withSum([
            'compartments as xl_total' => fn ($q) => $q
                ->where('size_category', 'xl')
                ->where('activation_status', 'active'),
        ], 'quantity');
    }

    private function applyCompartmentEagerLoad(Builder $query): void
    {
        $query->with([
            'compartments' => fn ($q) => $q
                ->orderByRaw("case size_category when 'small' then 1 when 'medium' then 2 when 'large' then 3 else 4 end")
                ->orderBy('sort_order')
                ->orderBy('id'),
        ]);
    }

    private function transformLocker(Locker $locker, bool $includeCompartments = false): array
    {
        $smallTotal = (int) ($locker->small_total ?? 0);
        $mediumTotal = (int) ($locker->medium_total ?? 0);
        $largeTotal = (int) ($locker->large_total ?? 0);
        $xlTotal = (int) ($locker->xl_total ?? 0);
        $totalCompartments = $smallTotal + $mediumTotal + $largeTotal + $xlTotal;

        $data = [
            'id' => $locker->id,
            'user_id' => $locker->user_id,
            'code' => $locker->code,
            'locker_name' => $locker->locker_name ?: $locker->code,
            'locker_type' => $locker->locker_type ?: Locker::TYPE_FACILITY_OWNED,
            'locker_type_label' => $this->typeLabel($locker->locker_type ?: Locker::TYPE_FACILITY_OWNED),
            'facility_name' => $locker->facility_name,
            'location' => $locker->location,
            'status' => $locker->status,
            'capacity' => (int) ($locker->capacity ?? 0),
            'notes' => $locker->notes,
            'last_checked_at' => $locker->last_checked_at,
            'small_total' => $smallTotal,
            'medium_total' => $mediumTotal,
            'large_total' => $largeTotal,
            'xl_total' => $xlTotal,
            'total_compartments' => $totalCompartments,
            'updated_at' => $locker->updated_at,
            'created_at' => $locker->created_at,
        ];

        if ($includeCompartments) {
            $compartments = $locker->relationLoaded('compartments')
                ? $locker->compartments
                : $locker->compartments()->orderBy('sort_order')->orderBy('id')->get();
            $data['compartments'] = $compartments
                ->map(fn ($row) => [
                    'id' => $row->id,
                    'size_category' => $row->size_category,
                    'compartment_code' => $row->compartment_code,
                    'quantity' => (int) $row->quantity,
                    'activation_status' => $row->activation_status,
                    'sort_order' => (int) ($row->sort_order ?? 0),
                ])
                ->values();
        }

        return $data;
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            Locker::TYPE_THIRD_PARTY_OWNED => 'Third Party Owned',
            default => 'Facility Owned',
        };
    }
}
