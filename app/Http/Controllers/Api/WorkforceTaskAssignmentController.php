<?php
namespace App\Http\Controllers\Api;
use App\Models\WorkforceTaskAssignment;
class WorkforceTaskAssignmentController extends WorkforceWorkflowRecordController { protected string $model = WorkforceTaskAssignment::class; protected string $numberPrefix = 'TA'; }
