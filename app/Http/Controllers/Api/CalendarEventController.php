<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarEventController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = CalendarEvent::query()
            ->with(['user:id,name,username,email'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('visibility', 'company')
                    ->orWhere('visibility', 'team');
            });

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $start = $request->get('start');
        $end = $request->get('end');
        if ($start && $end) {
            try {
                $startAt = Carbon::parse($start)->startOfDay();
                $endAt = Carbon::parse($end)->endOfDay();
                $query->where('start_at', '<=', $endAt)
                    ->where(function ($q) use ($startAt) {
                        $q->whereNull('end_at')->orWhere('end_at', '>=', $startAt);
                    });
            } catch (\Throwable $e) {
                // ignore invalid range
            }
        }

        $items = $query->orderBy('start_at')->get();

        if ($start && $end) {
            $items = $this->expandRecurring($items, Carbon::parse($start), Carbon::parse($end));
        }

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function show(Request $request, CalendarEvent $event)
    {
        if ($response = $this->denyUnlessOwns($request, $event)) {
            return $response;
        }

        $event->loadMissing(['user:id,name,username,email']);
        return response()->json(['success' => true, 'data' => $event]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'all_day' => ['required', 'boolean'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'location' => ['nullable', 'string', 'max:255'],
            'location_map' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'string', 'max:30'],
            'visibility' => ['nullable', 'string', 'max:30'],
            'priority' => ['nullable', 'in:low,medium,high,critical'],
            'event_type' => ['nullable', 'string', 'max:50'],
            'required_action' => ['nullable', 'string', 'max:255'],
            'recurrence_freq' => ['nullable', 'string', 'max:20'],
            'recurrence_interval' => ['nullable', 'integer', 'min:1', 'max:365'],
            'recurrence_days_json' => ['nullable', 'array'],
            'recurrence_days_json.*' => ['in:sun,mon,tue,wed,thu,fri,sat'],
            'recurrence_rules_json' => ['nullable', 'array'],
            'recurrence_mode' => ['nullable', 'in:none,after,by'],
            'recurrence_end_after' => ['nullable', 'integer', 'min:1', 'max:999', 'required_if:recurrence_mode,after'],
            'recurrence_until' => ['nullable', 'date', 'required_if:recurrence_mode,by'],
            'reminders_json' => ['nullable', 'array'],
            'attendees_json' => ['nullable', 'array'],
            'attendees_json.*' => ['email', 'max:255'],
            'exceptions_json' => ['nullable', 'array'],
            'exceptions_json.*' => ['date'],
        ]);

        $data = $this->normalizeRecurrencePayload($data);
        $data['user_id'] = $request->user()->id;
        $event = CalendarEvent::create($data);
        $event->load(['user:id,name,username,email']);

        return response()->json(['success' => true, 'data' => $event], 201);
    }

    public function update(Request $request, CalendarEvent $event)
    {
        if ($response = $this->denyUnlessOwns($request, $event)) {
            return $response;
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'all_day' => ['required', 'boolean'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'location' => ['nullable', 'string', 'max:255'],
            'location_map' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'string', 'max:30'],
            'visibility' => ['nullable', 'string', 'max:30'],
            'priority' => ['nullable', 'in:low,medium,high,critical'],
            'event_type' => ['nullable', 'string', 'max:50'],
            'required_action' => ['nullable', 'string', 'max:255'],
            'recurrence_freq' => ['nullable', 'string', 'max:20'],
            'recurrence_interval' => ['nullable', 'integer', 'min:1', 'max:365'],
            'recurrence_days_json' => ['nullable', 'array'],
            'recurrence_days_json.*' => ['in:sun,mon,tue,wed,thu,fri,sat'],
            'recurrence_rules_json' => ['nullable', 'array'],
            'recurrence_mode' => ['nullable', 'in:none,after,by'],
            'recurrence_end_after' => ['nullable', 'integer', 'min:1', 'max:999', 'required_if:recurrence_mode,after'],
            'recurrence_until' => ['nullable', 'date', 'required_if:recurrence_mode,by'],
            'reminders_json' => ['nullable', 'array'],
            'attendees_json' => ['nullable', 'array'],
            'attendees_json.*' => ['email', 'max:255'],
            'exceptions_json' => ['nullable', 'array'],
            'exceptions_json.*' => ['date'],
        ]);

        $data = $this->normalizeRecurrencePayload($data);
        $event->update($data);
        $event->load(['user:id,name,username,email']);

        return response()->json(['success' => true, 'data' => $event]);
    }

    public function destroy(Request $request, CalendarEvent $event)
    {
        if ($response = $this->denyUnlessOwns($request, $event)) {
            return $response;
        }

        $event->delete();

        return response()->json(['success' => true]);
    }

    public function export(Request $request)
    {
        $user = $request->user();
        $items = CalendarEvent::query()
            ->where('user_id', $user->id)
            ->orderBy('start_at')
            ->get();

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Contractor.com//Calendar//EN',
        ];

        foreach ($items as $event) {
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:' . $event->id . '@contractor.com';
            $lines[] = 'DTSTAMP:' . now()->utc()->format('Ymd\\THis\\Z');
            $lines[] = 'DTSTART:' . $event->start_at->utc()->format('Ymd\\THis\\Z');
            if ($event->end_at) {
                $lines[] = 'DTEND:' . $event->end_at->utc()->format('Ymd\\THis\\Z');
            }
            $lines[] = 'SUMMARY:' . $this->escapeIcs($event->title);
            if ($event->description) {
                $lines[] = 'DESCRIPTION:' . $this->escapeIcs($event->description);
            }
            if ($event->location) {
                $lines[] = 'LOCATION:' . $this->escapeIcs($event->location);
            }
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return response(implode("\r\n", $lines), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="calendar.ics"',
        ]);
    }

    public function import(Request $request)
    {
        $data = $request->validate([
            'ics' => ['required', 'string'],
        ]);

        $user = $request->user();
        $content = $data['ics'];
        $events = $this->parseIcs($content);
        $created = [];

        foreach ($events as $event) {
            $created[] = CalendarEvent::create([
                'user_id' => $user->id,
                'title' => $event['title'] ?? 'Imported Event',
                'description' => $event['description'] ?? null,
                'start_at' => $event['start_at'],
                'end_at' => $event['end_at'] ?? null,
                'all_day' => false,
                'timezone' => $event['timezone'] ?? null,
                'location' => $event['location'] ?? null,
                'location_map' => null,
                'color' => '#3b82f6',
                'status' => 'scheduled',
                'visibility' => 'private',
                'priority' => 'medium',
                'event_type' => 'general',
                'required_action' => null,
                'recurrence_days_json' => null,
                'recurrence_rules_json' => null,
                'recurrence_mode' => 'none',
                'recurrence_end_after' => null,
            ]);
        }

        return response()->json(['success' => true, 'data' => $created]);
    }

    protected function expandRecurring($items, Carbon $startAt, Carbon $endAt)
    {
        $expanded = collect();
        foreach ($items as $event) {
            $expanded->push($event);
            if (!$event->recurrence_freq) {
                continue;
            }

            $interval = $event->recurrence_interval ?: 1;
            $until = $event->recurrence_until ? Carbon::parse($event->recurrence_until)->endOfDay() : null;
            $duration = $event->end_at ? Carbon::parse($event->end_at)->diffInSeconds($event->start_at) : 0;
            $exceptions = collect($event->exceptions_json ?: [])->map(function ($value) {
                return Carbon::parse($value)->format('Y-m-d');
            })->all();

            $cursor = Carbon::parse($event->start_at);

            $weekdays = $this->normalizeWeekdayValues($event->recurrence_days_json);
            $rules = is_array($event->recurrence_rules_json) ? $event->recurrence_rules_json : [];
            if (!$until && ($event->recurrence_mode === 'after') && !empty($event->recurrence_end_after)) {
                $computedUntil = $this->computeRecurrenceUntilByOccurrences(
                    Carbon::parse($event->start_at),
                    (string) $event->recurrence_freq,
                    (int) $interval,
                    (int) $event->recurrence_end_after,
                    $weekdays,
                    $rules
                );
                if ($computedUntil) {
                    $until = Carbon::parse($computedUntil)->endOfDay();
                }
            }
            if (!$until) {
                $until = $endAt->copy();
            }
            $rangeEnd = $until->lt($endAt) ? $until->copy() : $endAt->copy();

            if ($event->recurrence_freq === 'weekly' && count($weekdays) > 0) {
                $baseStart = Carbon::parse($event->start_at);
                $baseDay = $baseStart->copy()->startOfDay();
                $walker = $baseDay->copy();
                if ($walker->lt($startAt->copy()->startOfDay())) {
                    $walker = $startAt->copy()->startOfDay();
                }

                while ($walker->lessThanOrEqualTo($rangeEnd->copy()->startOfDay())) {
                    $daysSinceBase = $baseDay->diffInDays($walker);
                    $weekIndex = intdiv($daysSinceBase, 7);
                    $isIntervalWeek = $weekIndex % $interval === 0;
                    $isSelectedDay = in_array($walker->dayOfWeek, $weekdays, true);

                    if ($isIntervalWeek && $isSelectedDay) {
                        $occurrence = $walker->copy()->setTime(
                            $baseStart->hour,
                            $baseStart->minute,
                            $baseStart->second
                        );

                        if ($occurrence->greaterThan($event->start_at)) {
                            if (!in_array($occurrence->format('Y-m-d'), $exceptions, true)) {
                                $instance = $event->replicate();
                                $instance->id = (string) $event->id . ':' . $occurrence->format('YmdHi');
                                $instance->start_at = $occurrence->copy();
                                $instance->end_at = $duration ? $occurrence->copy()->addSeconds($duration) : null;
                                $instance->is_instance = true;
                                if ($event->relationLoaded('user')) {
                                    $instance->setRelation('user', $event->user);
                                }
                                $expanded->push($instance);
                            }
                        }
                    }

                    $walker->addDay();
                }

                continue;
            }

            while ($cursor->lessThanOrEqualTo($rangeEnd)) {
                if ($cursor->greaterThan($event->start_at)) {
                    if (in_array($cursor->format('Y-m-d'), $exceptions, true)) {
                        $cursor = $this->nextRecurrence(
                            $event->recurrence_freq,
                            $cursor,
                            $interval,
                            $rules,
                            Carbon::parse($event->start_at)
                        );
                        continue;
                    }
                    $instance = $event->replicate();
                    $instance->id = (string) $event->id . ':' . $cursor->format('YmdHi');
                    $instance->start_at = $cursor->copy();
                    $instance->end_at = $duration ? $cursor->copy()->addSeconds($duration) : null;
                    $instance->is_instance = true;
                    if ($event->relationLoaded('user')) {
                        $instance->setRelation('user', $event->user);
                    }
                    $expanded->push($instance);
                }
                $cursor = $this->nextRecurrence(
                    $event->recurrence_freq,
                    $cursor,
                    $interval,
                    $rules,
                    Carbon::parse($event->start_at)
                );
                if (!$cursor) {
                    break;
                }
            }
        }

        return $expanded->sortBy('start_at')->values();
    }

    protected function nextRecurrence(
        ?string $freq,
        Carbon $cursor,
        int $interval,
        array $rules = [],
        ?Carbon $startAt = null
    ): ?Carbon {
        if ($freq === 'daily') {
            return $cursor->addDays($interval);
        }
        if ($freq === 'weekly') {
            return $cursor->addWeeks($interval);
        }
        if ($freq === 'monthly') {
            $base = $cursor->copy()->addMonthsNoOverflow($interval);
            $targetDay = (int) ($rules['monthly_day_of_month'] ?? ($startAt?->day ?? $cursor->day));
            $targetDay = max(1, min(31, $targetDay));
            $base->day(min($targetDay, $base->daysInMonth));
            if ($startAt) {
                $base->setTime($startAt->hour, $startAt->minute, $startAt->second);
            }
            return $base;
        }
        if ($freq === 'yearly') {
            $targetMonth = (int) ($rules['yearly_month'] ?? ($startAt?->month ?? $cursor->month));
            $targetDay = (int) ($rules['yearly_day_of_month'] ?? ($startAt?->day ?? $cursor->day));
            $targetMonth = max(1, min(12, $targetMonth));
            $targetDay = max(1, min(31, $targetDay));

            $next = $cursor->copy()->addYears($interval);
            $next->month($targetMonth)->day(1);
            $next->day(min($targetDay, $next->daysInMonth));
            if ($startAt) {
                $next->setTime($startAt->hour, $startAt->minute, $startAt->second);
            }
            return $next;
        }
        return null;
    }

    protected function normalizeRecurrencePayload(array $data): array
    {
        $data['event_type'] = $data['event_type'] ?? 'general';
        $data['required_action'] = $data['required_action'] ?? null;
        $data['recurrence_mode'] = $data['recurrence_mode'] ?? 'none';

        if (empty($data['recurrence_freq'])) {
            $data['recurrence_interval'] = null;
            $data['recurrence_days_json'] = null;
            $data['recurrence_rules_json'] = null;
            $data['recurrence_mode'] = 'none';
            $data['recurrence_end_after'] = null;
            $data['recurrence_until'] = null;
            return $data;
        }

        $data['recurrence_interval'] = max(1, (int) ($data['recurrence_interval'] ?? 1));

        if (($data['recurrence_freq'] ?? null) === 'weekly') {
            $days = $this->normalizeWeekdayValues($data['recurrence_days_json'] ?? []);
            if (count($days) === 0 && !empty($data['start_at'])) {
                try {
                    $days = [Carbon::parse($data['start_at'])->dayOfWeek];
                } catch (\Throwable $e) {
                    $days = [1]; // Monday fallback
                }
            }
            $weekdayLookup = [0 => 'sun', 1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat'];
            $data['recurrence_days_json'] = collect($days)->map(fn($day) => $weekdayLookup[$day] ?? null)->filter()->values()->all();
            $data['recurrence_rules_json'] = null;
        } elseif (($data['recurrence_freq'] ?? null) === 'monthly') {
            $startAt = !empty($data['start_at']) ? Carbon::parse($data['start_at']) : Carbon::now();
            $monthlyDay = (int) (($data['recurrence_rules_json']['monthly_day_of_month'] ?? null) ?: $startAt->day);
            $data['recurrence_days_json'] = null;
            $data['recurrence_rules_json'] = [
                'monthly_day_of_month' => max(1, min(31, $monthlyDay)),
            ];
        } elseif (($data['recurrence_freq'] ?? null) === 'yearly') {
            $startAt = !empty($data['start_at']) ? Carbon::parse($data['start_at']) : Carbon::now();
            $yearlyMonth = (int) (($data['recurrence_rules_json']['yearly_month'] ?? null) ?: $startAt->month);
            $yearlyDay = (int) (($data['recurrence_rules_json']['yearly_day_of_month'] ?? null) ?: $startAt->day);
            $data['recurrence_days_json'] = null;
            $data['recurrence_rules_json'] = [
                'yearly_month' => max(1, min(12, $yearlyMonth)),
                'yearly_day_of_month' => max(1, min(31, $yearlyDay)),
            ];
        } else {
            $data['recurrence_days_json'] = null;
            $data['recurrence_rules_json'] = null;
        }

        $mode = $data['recurrence_mode'] ?? 'none';

        if ($mode === 'after') {
            $data['recurrence_end_after'] = max(1, (int) ($data['recurrence_end_after'] ?? 10));
            try {
                $startAt = Carbon::parse($data['start_at']);
                $weekdays = $this->normalizeWeekdayValues($data['recurrence_days_json'] ?? []);
                $data['recurrence_until'] = $this->computeRecurrenceUntilByOccurrences(
                    $startAt,
                    (string) $data['recurrence_freq'],
                    (int) $data['recurrence_interval'],
                    (int) $data['recurrence_end_after'],
                    $weekdays,
                    is_array($data['recurrence_rules_json'] ?? null) ? $data['recurrence_rules_json'] : []
                );
            } catch (\Throwable $e) {
                $data['recurrence_until'] = null;
            }
        } elseif ($mode === 'by') {
            $data['recurrence_end_after'] = null;
        } else {
            $data['recurrence_mode'] = 'none';
            $data['recurrence_end_after'] = null;
            $data['recurrence_until'] = null;
        }

        return $data;
    }

    protected function computeRecurrenceUntilByOccurrences(
        Carbon $startAt,
        string $freq,
        int $interval,
        int $occurrences,
        array $weekdays = [],
        array $rules = []
    ): ?string {
        $interval = max(1, $interval);
        $targetCount = max(1, $occurrences);
        $cursor = $startAt->copy();

        if ($freq === 'weekly') {
            $days = count($weekdays) ? $weekdays : [$startAt->dayOfWeek];
            if (!in_array($startAt->dayOfWeek, $days, true)) {
                $days[] = $startAt->dayOfWeek;
                sort($days);
            }

            $baseDay = $startAt->copy()->startOfDay();
            $matched = 1;

            while ($matched < $targetCount) {
                $cursor->addDay();
                $cursorDay = $cursor->copy()->startOfDay();
                $daysSinceBase = $baseDay->diffInDays($cursorDay);
                $weekIndex = intdiv($daysSinceBase, 7);
                $isWeekMatch = $weekIndex % $interval === 0;
                $isDayMatch = in_array($cursor->dayOfWeek, $days, true);
                if ($isWeekMatch && $isDayMatch) {
                    $matched++;
                }
            }

            return $cursor->toDateString();
        }

        for ($i = 1; $i < $targetCount; $i++) {
            $next = $this->nextRecurrence($freq, $cursor, $interval, $rules, $startAt);
            if (!$next) {
                return null;
            }
            $cursor = $next;
        }

        return $cursor->toDateString();
    }

    protected function normalizeWeekdayValues($values): array
    {
        if (!is_array($values)) {
            return [];
        }

        $lookup = [
            'sun' => 0,
            'sunday' => 0,
            0 => 0,
            'mon' => 1,
            'monday' => 1,
            1 => 1,
            'tue' => 2,
            'tuesday' => 2,
            2 => 2,
            'wed' => 3,
            'wednesday' => 3,
            3 => 3,
            'thu' => 4,
            'thursday' => 4,
            4 => 4,
            'fri' => 5,
            'friday' => 5,
            5 => 5,
            'sat' => 6,
            'saturday' => 6,
            6 => 6,
        ];

        $normalized = [];
        foreach ($values as $value) {
            $key = is_string($value) ? strtolower(trim($value)) : $value;
            if (!array_key_exists($key, $lookup)) {
                continue;
            }
            $normalized[] = $lookup[$key];
        }

        $normalized = array_values(array_unique($normalized));
        sort($normalized);
        return $normalized;
    }

    protected function escapeIcs(string $value): string
    {
        return str_replace(["\\", ";", ",", "\n"], ["\\\\", "\\;", "\\,", "\\n"], $value);
    }

    protected function parseIcs(string $content): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $events = [];
        $current = null;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === 'BEGIN:VEVENT') {
                $current = [];
            } elseif ($line === 'END:VEVENT') {
                if ($current && isset($current['start_at'])) {
                    $events[] = $current;
                }
                $current = null;
            } elseif ($current !== null) {
                if (str_starts_with($line, 'DTSTART')) {
                    $current['start_at'] = Carbon::parse(substr($line, strpos($line, ':') + 1));
                } elseif (str_starts_with($line, 'DTEND')) {
                    $current['end_at'] = Carbon::parse(substr($line, strpos($line, ':') + 1));
                } elseif (str_starts_with($line, 'SUMMARY')) {
                    $current['title'] = substr($line, strpos($line, ':') + 1);
                } elseif (str_starts_with($line, 'DESCRIPTION')) {
                    $current['description'] = substr($line, strpos($line, ':') + 1);
                } elseif (str_starts_with($line, 'LOCATION')) {
                    $current['location'] = substr($line, strpos($line, ':') + 1);
                }
            }
        }
        return $events;
    }
}
