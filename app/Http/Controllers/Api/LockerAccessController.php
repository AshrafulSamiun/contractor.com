<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Locker;
use App\Models\LockerAccess;
use App\Models\Recipient;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LockerAccessController extends Controller
{
    public function index(Request $request)
    {
        $query = LockerAccess::query()
            ->where('user_id', $request->user()->id)
            ->with(['locker', 'recipient'])
            ->orderByDesc('updated_at');

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(function ($q) use ($term) {
                $q->whereHas('recipient', function ($sub) use ($term) {
                    $sub->where('recipient_name', 'like', "%{$term}%");
                })->orWhereHas('locker', function ($sub) use ($term) {
                    $sub->where('code', 'like', "%{$term}%");
                });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'locker_id' => ['required', 'integer'],
            'recipient_id' => ['required', 'integer'],
            'access_type' => ['required', 'in:temporary,permanent'],
            'status' => ['nullable', 'in:active,pending,expired,revoked'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $locker = Locker::where('user_id', $request->user()->id)->findOrFail($data['locker_id']);
        $recipient = Recipient::where('user_id', $request->user()->id)->findOrFail($data['recipient_id']);

        $status = $data['status'] ?? 'active';
        $now = Carbon::now();
        if (!empty($data['starts_at']) && Carbon::parse($data['starts_at'])->isFuture()) {
            $status = 'pending';
        }
        if (!empty($data['ends_at']) && Carbon::parse($data['ends_at'])->isPast()) {
            $status = 'expired';
        }

        $access = LockerAccess::create([
            'user_id' => $request->user()->id,
            'locker_id' => $locker->id,
            'recipient_id' => $recipient->id,
            'access_type' => $data['access_type'],
            'status' => $status,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json(['success' => true, 'data' => $access], 201);
    }

    public function update(Request $request, LockerAccess $lockerAccess)
    {
        if ($response = $this->denyUnlessOwns($request, $lockerAccess)) {
            return $response;
        }

        $data = $request->validate([
            'access_type' => ['nullable', 'in:temporary,permanent'],
            'status' => ['nullable', 'in:active,pending,expired,revoked'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'last_used_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $lockerAccess->update($data);

        return response()->json(['success' => true, 'data' => $lockerAccess]);
    }

    public function destroy(Request $request, LockerAccess $lockerAccess)
    {
        if ($response = $this->denyUnlessOwns($request, $lockerAccess)) {
            return $response;
        }

        $lockerAccess->delete();

        return response()->json(['success' => true]);
    }
}
