<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\ParcelStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ParcelStorageController extends Controller
{
    public function index(Request $request)
    {
        $query = ParcelStorage::query()
            ->where('user_id', $request->user()->id)
            ->with(['facility:id,user_id,facility_name']);

        if ($request->filled('storage_name')) {
            $query->where('storage_name', 'like', '%' . $request->storage_name . '%');
        }
        if ($request->filled('facility_id')) {
            $query->where('facility_id', (int) $request->facility_id);
        }
        if ($request->filled('facility_name')) {
            $facilityName = trim((string) $request->input('facility_name'));
            $query->where(function (Builder $subQuery) use ($facilityName) {
                $subQuery
                    ->where('facility_name', 'like', '%' . $facilityName . '%')
                    ->orWhereHas('facility', function (Builder $facilityQuery) use ($facilityName) {
                        $facilityQuery->where('facility_name', 'like', '%' . $facilityName . '%');
                    });
            });
        }
        if ($request->filled('floor_no')) {
            $query->where('floor_no', 'like', '%' . $request->floor_no . '%');
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'true' || $request->is_active === '1');
        }

        $perPage = min(max((int) $request->integer('per_page', 8), 1), 100);
        $page = max((int) $request->integer('page', 1), 1);
        $items = $query->orderByDesc('created_at')->paginate($perPage, ['*'], 'page', $page);
        $rows = $items->getCollection()
            ->map(fn (ParcelStorage $storage) => $this->transformStorage($storage))
            ->values();

        return response()->json([
            'success' => true,
            'data' => $rows,
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
            ],
        ]);
    }

    public function show(Request $request, ParcelStorage $parcelStorage)
    {
        if ($response = $this->denyUnlessOwns($request, $parcelStorage)) {
            return $response;
        }

        $parcelStorage->loadMissing(['facility:id,user_id,facility_name']);

        return response()->json(['success' => true, 'data' => $this->transformStorage($parcelStorage)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'storage_name' => ['required', 'string', 'max:255'],
            'facility_id' => [
                'required',
                'integer',
                Rule::exists('facilities', 'id')->where(function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                }),
            ],
            'floor_no' => ['nullable', 'string', 'max:255'],
            'area_section' => ['nullable', 'string', 'max:255'],
            'dedicated_item' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'note' => ['nullable', 'string', 'max:2000'],
        ], [
            'facility_id.required' => 'Facility is required.',
            'facility_id.exists' => 'Select a valid facility.',
        ]);

        $data = $this->syncFacilityFields($request, $data);
        $data['user_id'] = $request->user()->id;

        $storage = ParcelStorage::create($data);
        $storage->loadMissing(['facility:id,user_id,facility_name']);

        return response()->json(['success' => true, 'data' => $this->transformStorage($storage)], 201);
    }

    public function update(Request $request, ParcelStorage $parcelStorage)
    {
        if ($response = $this->denyUnlessOwns($request, $parcelStorage)) {
            return $response;
        }

        $data = $request->validate([
            'storage_name' => ['required', 'string', 'max:255'],
            'facility_id' => [
                'required',
                'integer',
                Rule::exists('facilities', 'id')->where(function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                }),
            ],
            'floor_no' => ['nullable', 'string', 'max:255'],
            'area_section' => ['nullable', 'string', 'max:255'],
            'dedicated_item' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'note' => ['nullable', 'string', 'max:2000'],
        ], [
            'facility_id.required' => 'Facility is required.',
            'facility_id.exists' => 'Select a valid facility.',
        ]);

        $data = $this->syncFacilityFields($request, $data);
        $parcelStorage->update($data);
        $parcelStorage->loadMissing(['facility:id,user_id,facility_name']);

        return response()->json(['success' => true, 'data' => $this->transformStorage($parcelStorage)]);
    }

    public function destroy(Request $request, ParcelStorage $parcelStorage)
    {
        if ($response = $this->denyUnlessOwns($request, $parcelStorage)) {
            return $response;
        }

        $parcelStorage->delete();

        return response()->json(['success' => true]);
    }

    protected function syncFacilityFields(Request $request, array $data): array
    {
        $facilityId = (int) ($data['facility_id'] ?? 0);
        $facility = Facility::query()
            ->where('user_id', $request->user()->id)
            ->find($facilityId);

        if (!$facility) {
            throw ValidationException::withMessages([
                'facility_id' => 'Select a valid facility.',
            ]);
        }

        $data['facility_id'] = (int) $facility->id;
        $data['facility_name'] = $facility->facility_name;

        return $data;
    }

    protected function transformStorage(ParcelStorage $storage): array
    {
        return [
            'id' => $storage->id,
            'user_id' => $storage->user_id,
            'storage_name' => $storage->storage_name,
            'facility_id' => $storage->facility_id,
            'facility_name' => $storage->facility?->facility_name ?: $storage->facility_name,
            'floor_no' => $storage->floor_no,
            'area_section' => $storage->area_section,
            'dedicated_item' => $storage->dedicated_item,
            'is_active' => (bool) $storage->is_active,
            'note' => $storage->note,
            'created_at' => $storage->created_at,
            'updated_at' => $storage->updated_at,
        ];
    }
}
