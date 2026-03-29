<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use App\Models\ParcelStatusHistory;
use App\Services\NotificationDispatcher;
use Illuminate\Http\Request;

class ParcelController extends Controller
{
    public function index(Request $request)
    {
        $query = Parcel::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at');
        if ($request->filled('status_in')) {
            $statusList = collect(explode(',', $request->string('status_in')->toString()))
                ->map(fn ($s) => trim($s))
                ->filter()
                ->values()
                ->all();
            if ($statusList) {
                $query->whereIn('status', $statusList);
            }
        } elseif ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('recipient_name', 'like', "%{$term}%")
                    ->orWhere('tracking_code', 'like', "%{$term}%")
                    ->orWhere('facility_name', 'like', "%{$term}%");
            });
        }
        if ($request->filled('from')) {
            $query->whereDate('updated_at', '>=', $request->string('from')->toString());
        }
        if ($request->filled('to')) {
            $query->whereDate('updated_at', '<=', $request->string('to')->toString());
        }
        $perPage = (int) $request->get('per_page', 15);
        return response()->json([
            'success' => true,
            'data' => $query->paginate($perPage),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_name' => ['nullable', 'string', 'max:255'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'in:pending,picked_up,delivered,held,returned,rejected,lost,damaged,expired'],
            'location' => ['nullable', 'string', 'max:255'],
            'tracking_code' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'received_at' => ['nullable', 'date'],
            'picked_up_at' => ['nullable', 'date'],
            'delivered_at' => ['nullable', 'date'],
        ]);

        $payload = [
            ...$validated,
            'user_id' => $request->user()->id,
            'received_at' => $validated['received_at'] ?? now(),
        ];
        if (($payload['status'] ?? null) === 'delivered' && empty($payload['delivered_at'])) {
            $payload['delivered_at'] = now();
        }
        if (($payload['status'] ?? null) === 'picked_up' && empty($payload['picked_up_at'])) {
            $payload['picked_up_at'] = now();
        }
        if (($payload['status'] ?? null) === 'pending') {
            $payload['delivered_at'] = null;
            $payload['picked_up_at'] = null;
        }

        $parcel = Parcel::create($payload);

        ParcelStatusHistory::create([
            'parcel_id' => $parcel->id,
            'from_status' => null,
            'to_status' => $parcel->status,
            'note' => 'Parcel created',
        ]);

        $event = match ($parcel->status) {
            'pending' => 'parcel_arrival',
            'picked_up', 'delivered' => 'delivery_completed',
            'held' => 'pickup_request',
            'returned' => 'parcel_return',
            'rejected' => 'rejected_parcel',
            'lost', 'damaged' => 'lost_damaged',
            'expired' => 'expiry_reminder',
            default => null,
        };
        if ($event) {
            app(NotificationDispatcher::class)->sendParcelEvent($request->user(), $parcel, $event);
        }

        return response()->json([
            'success' => true,
            'data' => $parcel,
        ], 201);
    }

    public function show(Parcel $parcel)
    {
        $this->assertOwnedParcel($parcel);

        return response()->json([
            'success' => true,
            'data' => $parcel,
        ]);
    }

    public function update(Request $request, Parcel $parcel)
    {
        $this->assertOwnedParcel($parcel);

        $validated = $request->validate([
            'facility_name' => ['nullable', 'string', 'max:255'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:pending,picked_up,delivered,held,returned,rejected,lost,damaged,expired'],
            'location' => ['nullable', 'string', 'max:255'],
            'tracking_code' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'picked_up_at' => ['nullable', 'date'],
            'delivered_at' => ['nullable', 'date'],
        ]);

        $fromStatus = $parcel->status;
        $parcel->update($validated);
        if (array_key_exists('status', $validated)) {
            if ($parcel->status === 'delivered') {
                $parcel->delivered_at = now();
            }
            if ($parcel->status === 'picked_up') {
                $parcel->picked_up_at = now();
            }
            if ($parcel->status === 'pending') {
                $parcel->delivered_at = null;
                $parcel->picked_up_at = null;
            }
            $parcel->save();
        }

        if (array_key_exists('status', $validated)) {
            ParcelStatusHistory::create([
                'parcel_id' => $parcel->id,
                'from_status' => $fromStatus,
                'to_status' => $parcel->status,
                'note' => 'Status updated',
            ]);

            $event = match ($parcel->status) {
                'pending' => 'parcel_arrival',
                'picked_up', 'delivered' => 'delivery_completed',
                'held' => 'pickup_request',
                'returned' => 'parcel_return',
                'rejected' => 'rejected_parcel',
                'lost', 'damaged' => 'lost_damaged',
                'expired' => 'expiry_reminder',
                default => null,
            };
            if ($event) {
                app(NotificationDispatcher::class)->sendParcelEvent($request->user(), $parcel, $event);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $parcel,
        ]);
    }

    public function destroy(Parcel $parcel)
    {
        $this->assertOwnedParcel($parcel);

        $parcel->delete();
        return response()->json([
            'success' => true,
        ]);
    }

    public function scan(Request $request)
    {
        $validated = $request->validate([
            'tracking_code' => ['required', 'string'],
            'status' => ['required', 'in:pending,picked_up,delivered,held,returned,rejected,lost,damaged,expired'],
        ]);

        $parcel = Parcel::where('user_id', $request->user()->id)
            ->where('tracking_code', $validated['tracking_code'])
            ->first();
        if (!$parcel) {
            return response()->json([
                'message' => 'Parcel not found.',
            ], 404);
        }

        $fromStatus = $parcel->status;
        $parcel->status = $validated['status'];
        if ($validated['status'] === 'delivered') {
            $parcel->delivered_at = now();
        }
        if ($validated['status'] === 'picked_up') {
            $parcel->picked_up_at = now();
        }
        if ($validated['status'] === 'pending') {
            $parcel->delivered_at = null;
            $parcel->picked_up_at = null;
        }
        $parcel->save();

        ParcelStatusHistory::create([
            'parcel_id' => $parcel->id,
            'from_status' => $fromStatus,
            'to_status' => $parcel->status,
            'note' => 'Scanned update',
        ]);

        $event = match ($parcel->status) {
            'pending' => 'parcel_arrival',
            'picked_up', 'delivered' => 'delivery_completed',
            'held' => 'pickup_request',
            'returned' => 'parcel_return',
            'rejected' => 'rejected_parcel',
            'lost', 'damaged' => 'lost_damaged',
            'expired' => 'expiry_reminder',
            default => null,
        };
        if ($event) {
            app(NotificationDispatcher::class)->sendParcelEvent($request->user(), $parcel, $event);
        }

        return response()->json([
            'success' => true,
            'data' => $parcel,
        ]);
    }

    public function history(Parcel $parcel)
    {
        $this->assertOwnedParcel($parcel);

        $history = ParcelStatusHistory::where('parcel_id', $parcel->id)->orderByDesc('created_at')->get();
        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    private function assertOwnedParcel(Parcel $parcel): void
    {
        if ($parcel->user_id !== auth()->id()) {
            abort(404);
        }
    }
}
