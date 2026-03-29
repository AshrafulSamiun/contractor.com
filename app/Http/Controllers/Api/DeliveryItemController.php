<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryItem;
use Illuminate\Http\Request;

class DeliveryItemController extends Controller
{
    public function index(Request $request)
    {
        $query = DeliveryItem::query()->where('user_id', $request->user()->id);

        if ($request->filled('item_name')) {
            $query->where('item_name', 'like', '%' . $request->item_name . '%');
        }
        if ($request->filled('category')) {
            $category = trim((string) $request->input('category'));
            $query->where(function ($subQuery) use ($category) {
                $subQuery
                    ->whereJsonContains('categories', $category)
                    ->orWhere('categories', 'like', '%"' . $category . '"%');
            });
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'true' || $request->is_active === '1');
        }

        $perPage = min(max((int) $request->integer('per_page', 10), 1), 100);
        $page = max((int) $request->integer('page', 1), 1);
        $items = $query->orderByDesc('created_at')->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $items->items(),
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

    public function show(Request $request, DeliveryItem $deliveryItem)
    {
        if ($response = $this->denyUnlessOwns($request, $deliveryItem)) {
            return $response;
        }

        return response()->json(['success' => true, 'data' => $deliveryItem]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['string', 'max:50'],
            'is_active' => ['required', 'boolean'],
            'note' => ['nullable', 'string', 'max:300'],
        ]);

        $data['categories'] = $this->normalizeCategories($data['categories'] ?? []);
        $data['user_id'] = $request->user()->id;

        $item = DeliveryItem::create($data);

        return response()->json(['success' => true, 'data' => $item], 201);
    }

    public function update(Request $request, DeliveryItem $deliveryItem)
    {
        if ($response = $this->denyUnlessOwns($request, $deliveryItem)) {
            return $response;
        }

        $data = $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['string', 'max:50'],
            'is_active' => ['required', 'boolean'],
            'note' => ['nullable', 'string', 'max:300'],
        ]);

        $data['categories'] = $this->normalizeCategories($data['categories'] ?? []);
        $deliveryItem->update($data);

        return response()->json(['success' => true, 'data' => $deliveryItem]);
    }

    public function destroy(Request $request, DeliveryItem $deliveryItem)
    {
        if ($response = $this->denyUnlessOwns($request, $deliveryItem)) {
            return $response;
        }

        $deliveryItem->delete();

        return response()->json(['success' => true]);
    }

    protected function normalizeCategories(array $categories): array
    {
        $cleaned = [];
        foreach ($categories as $category) {
            if (!is_string($category)) {
                continue;
            }
            $value = trim($category);
            if ($value === '') {
                continue;
            }
            $cleaned[] = $value;
        }

        return array_values(array_unique($cleaned));
    }
}
