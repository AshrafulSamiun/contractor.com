<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkforceStaffAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WorkforceStaffAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->scoped($request);

        foreach (['attendance_date' => 'date', 'location_site' => 'location', 'customer_job_site' => 'customer', 'department' => 'department', 'shift_name' => 'shift', 'status' => 'status'] as $column => $filter) {
            if ($request->filled($filter)) $query->where($column, $request->string($filter)->toString());
        }
        if ($request->filled('search')) {
            $term = '%' . $request->string('search')->toString() . '%';
            $query->where(fn ($q) => $q->where('report_no', 'like', $term)->orWhere('staff_name', 'like', $term)->orWhere('employee_id', 'like', $term)->orWhere('position_title', 'like', $term));
        }

        return response()->json(['success' => true, 'data' => $query->orderBy('staff_name')->get()->map(fn ($item) => $this->transform($item))->values()]);
    }

    public function store(Request $request)
    {
        $attendance = WorkforceStaffAttendance::create($this->validated($request) + [
            'user_id' => $request->user()->id,
            'project_id' => $request->user()->project_id,
            'prepared_by' => $request->input('prepared_by') ?: $request->user()->name,
        ]);
        if (!$attendance->report_no) {
            $attendance->update(['report_no' => 'SA-' . $attendance->attendance_date->format('Ymd') . '-' . str_pad((string) $attendance->id, 3, '0', STR_PAD_LEFT)]);
        }
        return response()->json(['success' => true, 'data' => $this->transform($attendance->fresh())], 201);
    }

    public function show(Request $request, WorkforceStaffAttendance $attendance)
    {
        $this->authorizeRecord($request, $attendance);
        return response()->json(['success' => true, 'data' => $this->transform($attendance)]);
    }

    public function update(Request $request, WorkforceStaffAttendance $attendance)
    {
        $this->authorizeRecord($request, $attendance);
        $attendance->update($this->validated($request));
        return response()->json(['success' => true, 'data' => $this->transform($attendance->fresh())]);
    }

    public function destroy(Request $request, WorkforceStaffAttendance $attendance)
    {
        $this->authorizeRecord($request, $attendance);
        $attendance->delete();
        return response()->json(['success' => true]);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'attendance_date' => ['required', 'date'], 'prepared_by' => ['nullable', 'string', 'max:120'],
            'staff_name' => ['required', 'string', 'max:120'], 'employee_id' => ['nullable', 'string', 'max:60'],
            'position_title' => ['nullable', 'string', 'max:120'], 'phone' => ['nullable', 'string', 'max:40'], 'email' => ['nullable', 'email', 'max:120'],
            'location_site' => ['nullable', 'string', 'max:120'], 'customer_job_site' => ['nullable', 'string', 'max:160'],
            'department' => ['nullable', 'string', 'max:120'], 'shift_name' => ['nullable', 'string', 'max:80'],
            'check_in_time' => ['nullable', 'date_format:H:i'], 'check_out_time' => ['nullable', 'date_format:H:i'],
            'total_minutes' => ['nullable', 'integer', 'min:0'], 'status' => ['required', 'string', 'max:30'],
            'overtime' => ['nullable', 'boolean'], 'late_arrival' => ['nullable', 'boolean'], 'early_departure' => ['nullable', 'boolean'],
            'work_performed' => ['nullable', 'string', 'max:5000'], 'notes' => ['nullable', 'string', 'max:5000'], 'remarks' => ['nullable', 'string', 'max:5000'],
        ]);
        if (!isset($data['total_minutes']) && !empty($data['check_in_time']) && !empty($data['check_out_time'])) {
            $start = Carbon::createFromFormat('H:i', $data['check_in_time']);
            $end = Carbon::createFromFormat('H:i', $data['check_out_time']);
            $data['total_minutes'] = $end->greaterThan($start) ? $start->diffInMinutes($end) : 0;
        }
        return $data;
    }

    private function scoped(Request $request)
    {
        return WorkforceStaffAttendance::query()->when($request->user()->project_id !== null, fn ($q) => $q->where('project_id', $request->user()->project_id), fn ($q) => $q->whereNull('project_id'));
    }

    private function authorizeRecord(Request $request, WorkforceStaffAttendance $attendance): void
    {
        abort_unless((string) $attendance->project_id === (string) $request->user()->project_id, 403, 'Unauthorized');
    }

    private function transform(WorkforceStaffAttendance $item): array
    {
        return array_merge($item->toArray(), [
            'check_in_time' => $item->check_in_time ? Carbon::parse($item->check_in_time)->format('H:i') : null,
            'check_out_time' => $item->check_out_time ? Carbon::parse($item->check_out_time)->format('H:i') : null,
            'total_hours' => sprintf('%dh %02dm', intdiv($item->total_minutes, 60), $item->total_minutes % 60),
        ]);
    }
}
