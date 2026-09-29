<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class WorkforceWorkflowRecordController extends Controller
{
    protected string $model;
    protected string $numberPrefix;

    public function index(Request $request)
    {
        $query = $this->model::query()->when($request->user()->project_id !== null, fn ($q) => $q->where('project_id', $request->user()->project_id), fn ($q) => $q->whereNull('project_id'));
        foreach (['record_date' => 'date', 'status' => 'status', 'department' => 'department', 'request_type' => 'type', 'priority' => 'priority', 'customer_job_site' => 'customer'] as $column => $filter) {
            if ($request->filled($filter)) $query->where($column, $request->string($filter)->toString());
        }
        if ($request->filled('staff')) {
            $staff = $request->string('staff')->toString();
            $query->where(fn ($q) => $q->where('staff_name', $staff)->orWhere('assigned_to', $staff));
        }
        if ($request->filled('date_from')) $query->whereDate('record_date', '>=', $request->date('date_from'));
        if ($request->filled('date_to')) $query->whereDate('record_date', '<=', $request->date('date_to'));
        if ($request->filled('search')) {
            $term = '%' . $request->string('search')->toString() . '%';
            $query->where(fn ($q) => $q->where('record_no', 'like', $term)->orWhere('title', 'like', $term)->orWhere('staff_name', 'like', $term)->orWhere('assigned_to', 'like', $term));
        }
        return response()->json(['success' => true, 'data' => $query->orderByDesc('record_date')->orderByDesc('id')->get()]);
    }

    public function show(Request $request, $record)
    {
        $record = $this->resolveRecord($record);
        $this->assertProject($request, $record);
        return response()->json(['success' => true, 'data' => $record]);
    }

    public function store(Request $request)
    {
        $record = $this->model::create($this->validated($request) + ['user_id' => $request->user()->id, 'project_id' => $request->user()->project_id]);
        $record->update(['record_no' => $this->numberPrefix . '-' . $record->record_date->format('Ymd') . '-' . str_pad((string) $record->id, 3, '0', STR_PAD_LEFT)]);
        return response()->json(['success' => true, 'data' => $record->fresh()], 201);
    }

    public function update(Request $request, $record)
    {
        $record = $this->resolveRecord($record);
        $this->assertProject($request, $record);
        $record->update($this->validated($request));
        return response()->json(['success' => true, 'data' => $record->fresh()]);
    }

    public function destroy(Request $request, $record)
    {
        $record = $this->resolveRecord($record);
        $this->assertProject($request, $record);
        $record->delete();
        return response()->json(['success' => true]);
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'record_date' => ['required', 'date'], 'title' => ['required', 'string', 'max:255'], 'request_type' => ['nullable', 'string', 'max:80'],
            'staff_name' => ['nullable', 'string', 'max:120'], 'assigned_to' => ['nullable', 'string', 'max:120'], 'location_site' => ['nullable', 'string', 'max:120'],
            'customer_job_site' => ['nullable', 'string', 'max:160'], 'department' => ['nullable', 'string', 'max:120'], 'start_at' => ['nullable', 'date'], 'end_at' => ['nullable', 'date'],
            'status' => ['required', 'string', 'max:30'], 'priority' => ['nullable', 'string', 'max:30'], 'notes' => ['nullable', 'string', 'max:5000'], 'details_json' => ['nullable', 'array'],
        ]);
    }

    private function assertProject(Request $request, Model $record): void
    {
        abort_unless((string) $record->project_id === (string) $request->user()->project_id, 403, 'Unauthorized');
    }

    private function resolveRecord($record): Model
    {
        return $record instanceof Model ? $record : $this->model::query()->findOrFail($record);
    }
}
