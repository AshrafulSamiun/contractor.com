<?php

namespace App\Services;

use App\Models\AccountLoginHistory;
use App\Models\AccountSecurityDevice;
use App\Models\AccountSecuritySession;
use App\Models\User;
use Illuminate\Http\Request;

class AccountSecurityActivityTracker
{
    public function recordLogin(User $user, Request $request, ?int $tokenId): void
    {
        if ((int) $user->project_id < 1) return;
        $device = $this->device($user, $request);
        AccountSecuritySession::updateOrCreate(['token_id' => $tokenId], ['project_id' => $user->project_id, 'user_id' => $user->id, 'device_id' => $device->id, 'ip_address' => $request->ip(), 'started_at' => now(), 'last_active_at' => now(), 'logged_out_at' => null]);
        AccountLoginHistory::create(['project_id' => $user->project_id, 'user_id' => $user->id, 'device_id' => $device->id, 'device_name' => $device->device_name, 'browser' => $device->browser, 'ip_address' => $request->ip(), 'location' => $device->location, 'result' => 'Successful', 'logged_in_at' => now()]);
    }
    public function touch(User $user, Request $request): void
    {
        $tokenId = $user->currentAccessToken()?->id;
        if (! $tokenId) return;
        $session = AccountSecuritySession::where('user_id', $user->id)->where('token_id', $tokenId)->first();
        if (! $session) {
            if ((int) $user->project_id < 1) return;
            $device = $this->device($user, $request);
            AccountSecuritySession::create(['project_id' => $user->project_id, 'user_id' => $user->id, 'device_id' => $device->id, 'token_id' => $tokenId, 'ip_address' => $request->ip(), 'started_at' => now(), 'last_active_at' => now()]);
            return;
        }
        if (! $session->logged_out_at) $session->update(['last_active_at' => now()]);
    }
    public function endToken(User $user, ?int $tokenId): void
    {
        if ($tokenId) AccountSecuritySession::where('user_id', $user->id)->where('token_id', $tokenId)->whereNull('logged_out_at')->update(['logged_out_at' => now()]);
    }
    private function device(User $user, Request $request): AccountSecurityDevice
    {
        $agent = substr((string) $request->userAgent(), 0, 1000); $rawKey = $request->header('X-Device-ID') ?: $agent.'|'.$request->ip(); $key = hash('sha256', $rawKey);
        $browser = str_contains($agent, 'Edg/') ? 'Edge' : (str_contains($agent, 'Firefox/') ? 'Firefox' : (str_contains($agent, 'Chrome/') ? 'Chrome' : (str_contains($agent, 'Safari/') ? 'Safari' : 'Unknown browser')));
        $type = preg_match('/iPhone|iPad|Android/i', $agent) ? (str_contains($agent, 'iPhone') || str_contains($agent, 'iPad') ? 'iOS' : 'Android') : 'Desktop';
        $name = $type === 'Desktop' ? $browser.' on Desktop' : $browser.' on '.$type; $location = $request->ip() ? 'IP: '.$request->ip() : 'Location unavailable';
        $device = AccountSecurityDevice::firstOrCreate(['user_id' => $user->id, 'device_key' => $key], ['project_id' => $user->project_id, 'device_name' => $name, 'device_type' => $type, 'browser' => $browser, 'user_agent' => $agent, 'ip_address' => $request->ip(), 'location' => $location, 'registered_at' => now()]);
        $device->update(['project_id' => $user->project_id, 'device_name' => $name, 'device_type' => $type, 'browser' => $browser, 'user_agent' => $agent, 'ip_address' => $request->ip(), 'location' => $location, 'last_seen_at' => now(), 'revoked_at' => null]);
        return $device;
    }
}
