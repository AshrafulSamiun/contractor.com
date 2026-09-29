<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FixedAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FixedAssetController extends Controller
{
    public function index(Request $request)
    {
        $query = FixedAsset::query()->where('project_id', $request->user()->project_id);
        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(fn ($q) => $q->where('asset_no', 'like', "%{$search}%")
                ->orWhere('asset_name', 'like', "%{$search}%")
                ->orWhere('asset_group', 'like', "%{$search}%")
                ->orWhere('asset_type', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%"));
        }
        return response()->json(['success' => true, 'data' => $query->latest()->get()]);
    }

    public function options()
    {
        return response()->json(['success' => true, 'data' => [
            'statuses' => FixedAsset::STATUSES,
            'asset_groups' => ['Heavy Equipment', 'Vehicles', 'Equipment', 'Material Handling', 'Furniture & Fixtures', 'Technology', 'Other'],
        ]]);
    }

    public function store(Request $request)
    {
        $projectId = $request->user()->project_id;
        $asset = DB::transaction(fn () => FixedAsset::create($this->validated($request) + [
            'project_id' => $projectId, 'asset_no' => $this->nextAssetNo($projectId),
            'created_by' => $request->user()->id,
        ]));
        return response()->json(['success' => true, 'data' => $asset], 201);
    }

    public function show(Request $request, FixedAsset $fixedAsset)
    {
        $this->authorizeAsset($request, $fixedAsset);
        return response()->json(['success' => true, 'data' => $fixedAsset]);
    }

    public function update(Request $request, FixedAsset $fixedAsset)
    {
        $this->authorizeAsset($request, $fixedAsset);
        $fixedAsset->update($this->validated($request) + ['updated_by' => $request->user()->id]);
        return response()->json(['success' => true, 'data' => $fixedAsset->fresh()]);
    }

    public function destroy(Request $request, FixedAsset $fixedAsset)
    {
        $this->authorizeAsset($request, $fixedAsset);
        $fixedAsset->delete();
        return response()->json(['success' => true]);
    }

    private function authorizeAsset(Request $request, FixedAsset $asset): void
    {
        abort_unless((int) $asset->project_id === (int) $request->user()->project_id, 404);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'asset_name' => ['required', 'string', 'max:255'], 'asset_group' => ['required', 'string', 'max:100'],
            'asset_type' => ['required', 'string', 'max:100'], 'location' => ['required', 'string', 'max:150'],
            'status' => ['required', Rule::in(FixedAsset::STATUSES)], 'purchase_date' => ['required', 'date'],
            'purchase_cost' => ['required', 'numeric', 'min:0'], 'book_value' => ['required', 'numeric', 'min:0'],
            'last_valuation_date' => ['nullable', 'date'], 'notes' => ['nullable', 'string'],
        ]);
    }

    private function nextAssetNo(?int $projectId): string
    {
        $last = FixedAsset::query()->where('project_id', $projectId)->where('asset_no', 'like', 'AST-%')
            ->lockForUpdate()->orderByDesc('id')->value('asset_no');
        return 'AST-'.str_pad((string) ($last ? (int) substr($last, 4) + 1 : 1), 6, '0', STR_PAD_LEFT);
    }
}
