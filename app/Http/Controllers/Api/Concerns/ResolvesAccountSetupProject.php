<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\AccountSetup;
use Illuminate\Http\Request;

trait ResolvesAccountSetupProject
{
    protected function accountSetupProjectId(Request $request): int
    {
        $projectId = AccountSetup::query()
            ->where('user_id', $request->user()->id)
            ->value('id');

        abort_if(! $projectId, 404, 'No account setup is linked to this user.');

        return (int) $projectId;
    }
}
