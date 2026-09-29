<?php
namespace App\Http\Controllers\Api;
use App\Models\WorkforceWorkSchedule;
class WorkforceWorkScheduleController extends WorkforceWorkflowRecordController { protected string $model = WorkforceWorkSchedule::class; protected string $numberPrefix = 'WS'; }
