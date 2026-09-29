<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountSecurity;
use App\Models\AccountSecurityDevice;
use App\Models\AccountSecuritySession;
use App\Models\AccountLoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AccountSecurityController extends Controller
{
    public function show(Request $request)
    {
        return response()->json(['success' => true, 'data' => $this->present($request, $this->security($request))]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'mfa_enabled' => ['sometimes', 'boolean'],
            'verification_method' => ['sometimes', 'string', 'max:100'],
        ]);

        $security = $this->security($request);
        $security->fill($validated + ['updated_by' => $request->user()->id])->save();

        return response()->json(['success' => true, 'data' => $this->present($request, $security->fresh())]);
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $user = $request->user();
        abort_unless(Hash::check($data['current_password'], $user->password), 422, 'Current password is incorrect.');

        $user->update(['password' => $data['new_password']]);
        $security = $this->security($request);
        $security->update(['password_changed_at' => now(), 'updated_by' => $user->id]);

        return response()->json(['success' => true, 'message' => 'Password changed. Please log in again.']);
    }

    public function changePin(Request $request)
    {
        $data = $request->validate([
            'current_pin' => ['required', 'string'],
            'security_pin' => ['required', 'string', 'min:4', 'max:20', 'confirmed'],
        ]);
        $user = $request->user();
        abort_unless($user->security_pin_code && Hash::check($data['current_pin'], $user->security_pin_code), 422, 'Current PIN is incorrect.');

        $user->update(['security_pin_code' => $data['security_pin']]);
        $this->security($request)->update(['pin_changed_at' => now(), 'updated_by' => $user->id]);

        return response()->json(['success' => true, 'message' => 'Security PIN changed. Please log in again.']);
    }

    public function removeDevice(Request $request, string $device)
    {
        $security = $this->security($request);
        $record = AccountSecurityDevice::where('project_id', $request->user()->project_id)->where('user_id', $request->user()->id)->findOrFail($device);
        $tokenIds = AccountSecuritySession::where('device_id', $record->id)->whereNull('logged_out_at')->pluck('token_id')->filter();
        PersonalAccessToken::whereIn('id', $tokenIds)->delete();
        AccountSecuritySession::where('device_id', $record->id)->whereNull('logged_out_at')->update(['logged_out_at' => now()]);
        $record->update(['revoked_at' => now()]);

        return response()->json(['success' => true, 'data' => $this->present($request, $security->fresh())]);
    }

    public function logoutAllDevices(Request $request)
    {
        $security = $this->security($request);
        $currentTokenId = $request->user()->currentAccessToken()?->id;
        $sessions = AccountSecuritySession::where('project_id', $request->user()->project_id)->where('user_id', $request->user()->id)->whereNull('logged_out_at')->when($currentTokenId, fn ($query) => $query->where('token_id', '!=', $currentTokenId));
        $tokenIds = $sessions->pluck('token_id')->filter();
        PersonalAccessToken::whereIn('id', $tokenIds)->delete();
        AccountSecuritySession::whereIn('token_id', $tokenIds)->update(['logged_out_at' => now()]);

        return response()->json(['success' => true, 'data' => $this->present($request, $security->fresh()), 'message' => 'All other devices were logged out.']);
    }

    private function present(Request $request, AccountSecurity $security): array
    {
        $user = $request->user();
        $currentTokenId = $user->currentAccessToken()?->id;
        return array_merge($security->toArray(), [
            'registered_devices' => AccountSecurityDevice::where('project_id', $user->project_id)->where('user_id', $user->id)->whereNull('revoked_at')->latest('last_seen_at')->get()->map(fn ($device) => ['id' => (string) $device->id, 'name' => $device->device_name, 'type' => $device->device_type, 'registered_date' => $device->registered_at?->toDateString(), 'status' => 'Active'])->values(),
            'active_sessions' => AccountSecuritySession::with('device')->where('project_id', $user->project_id)->where('user_id', $user->id)->whereNull('logged_out_at')->latest('last_active_at')->get()->map(fn ($session) => ['id' => (string) $session->id, 'device' => $session->device?->device_name ?? 'Unknown device', 'browser' => $session->device?->browser ?? 'Unknown browser', 'location' => $session->device?->location ?? ($session->ip_address ? 'IP: '.$session->ip_address : 'Location unavailable'), 'last_active' => $session->last_active_at?->toDateString(), 'status' => $session->token_id === $currentTokenId ? 'Current' : 'Active'])->values(),
            'login_history' => AccountLoginHistory::where('project_id', $user->project_id)->where('user_id', $user->id)->latest('logged_in_at')->limit(25)->get()->map(fn ($login) => ['date' => $login->logged_in_at?->toDateString(), 'device' => $login->device_name, 'location' => $login->location ?? ($login->ip_address ? 'IP: '.$login->ip_address : 'Location unavailable'), 'result' => $login->result])->values(),
        ]);
    }

    private function security(Request $request): AccountSecurity
    {
        $user = $request->user();
        abort_if((int) $user->project_id < 1, 404, 'No project is assigned to this user.');

        return AccountSecurity::firstOrCreate(
            ['project_id' => $user->project_id, 'user_id' => $user->id],
            [
                'inserted_by' => $user->id,
                'updated_by' => $user->id,
                'password_changed_at' => $user->updated_at,
                'pin_changed_at' => $user->updated_at,
            ]
        );
    }
}
