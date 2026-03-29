<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryMethod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeliveryMethodController extends Controller
{
    private const METHOD_TYPE_OPTIONS = [
        'Pick up at Front Desk',
        'Pick up from Facility Locker',
        'Pick up from External Locker',
        'Pick up from Courier Staff',
        'Delivered by Courier',
        'Delivered by Facility Staff',
    ];

    public function index(Request $request)
    {
        $query = DeliveryMethod::query()->where('user_id', $request->user()->id);

        if ($request->filled('method_name')) {
            $query->where('method_name', 'like', '%' . $request->method_name . '%');
        }
        if ($request->filled('method_type')) {
            $methodType = $this->normalizeMethodTypeValue($request->input('method_type'));
            if ($methodType !== null) {
                $query->where('method_type', $methodType);
            } else {
                $query->where('method_type', 'like', '%' . trim((string) $request->input('method_type')) . '%');
            }
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

    public function show(Request $request, DeliveryMethod $deliveryMethod)
    {
        if ($response = $this->denyUnlessOwns($request, $deliveryMethod)) {
            return $response;
        }

        return response()->json(['success' => true, 'data' => $deliveryMethod]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'method_type' => $this->normalizeMethodTypeValue($request->input('method_type')),
        ]);

        $data = $request->validate([
            'method_name' => ['required', 'string', 'max:255'],
            'method_type' => ['required', 'string', Rule::in(self::METHOD_TYPE_OPTIONS)],
            'is_active' => ['required', 'boolean'],
            'note' => ['nullable', 'string', 'max:300'],
        ], [
            'method_type.required' => 'Delivery method type is required.',
            'method_type.in' => 'Select a valid delivery method type.',
        ]);

        $data['user_id'] = $request->user()->id;

        $method = DeliveryMethod::create($data);

        return response()->json(['success' => true, 'data' => $method], 201);
    }

    public function update(Request $request, DeliveryMethod $deliveryMethod)
    {
        if ($response = $this->denyUnlessOwns($request, $deliveryMethod)) {
            return $response;
        }

        $request->merge([
            'method_type' => $this->normalizeMethodTypeValue($request->input('method_type')),
        ]);

        $data = $request->validate([
            'method_name' => ['required', 'string', 'max:255'],
            'method_type' => ['required', 'string', Rule::in(self::METHOD_TYPE_OPTIONS)],
            'is_active' => ['required', 'boolean'],
            'note' => ['nullable', 'string', 'max:300'],
        ], [
            'method_type.required' => 'Delivery method type is required.',
            'method_type.in' => 'Select a valid delivery method type.',
        ]);

        $deliveryMethod->update($data);

        return response()->json(['success' => true, 'data' => $deliveryMethod]);
    }

    public function destroy(Request $request, DeliveryMethod $deliveryMethod)
    {
        if ($response = $this->denyUnlessOwns($request, $deliveryMethod)) {
            return $response;
        }

        $deliveryMethod->delete();

        return response()->json(['success' => true]);
    }

    protected function normalizeMethodTypeValue(mixed $value): ?string
    {
        if (!is_string($value) && !is_numeric($value)) {
            return null;
        }

        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return null;
        }

        foreach (self::METHOD_TYPE_OPTIONS as $option) {
            if (strcasecmp($trimmed, $option) === 0) {
                return $option;
            }
        }

        $normalized = strtolower(preg_replace('/\s+/', ' ', $trimmed) ?? '');
        $aliases = [
            'pick up at front desk' => 'Pick up at Front Desk',
            'pickup at front desk' => 'Pick up at Front Desk',
            'front desk pickup' => 'Pick up at Front Desk',
            'pick up from facility locker' => 'Pick up from Facility Locker',
            'pickup from facility locker' => 'Pick up from Facility Locker',
            'pick up from external locker' => 'Pick up from External Locker',
            'pickup from external locker' => 'Pick up from External Locker',
            'pick up from courier staff' => 'Pick up from Courier Staff',
            'pickup from courier staff' => 'Pick up from Courier Staff',
            'delivered by courier' => 'Delivered by Courier',
            'delivered by facility staff' => 'Delivered by Facility Staff',
        ];

        return $aliases[$normalized] ?? null;
    }
}
