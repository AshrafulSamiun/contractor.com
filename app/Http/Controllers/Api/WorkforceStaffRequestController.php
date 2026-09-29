<?php
namespace App\Http\Controllers\Api;
use App\Models\WorkforceStaffRequest;
class WorkforceStaffRequestController extends WorkforceWorkflowRecordController { protected string $model = WorkforceStaffRequest::class; protected string $numberPrefix = 'SR'; }
