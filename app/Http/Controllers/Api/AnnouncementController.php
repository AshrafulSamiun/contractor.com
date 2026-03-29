<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementAttachment;
use App\Models\AnnouncementRead;
use App\Models\Facility;
use App\Models\Recipient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AnnouncementController extends Controller
{
    public function meta(Request $request)
    {
        $user = $request->user();

        $facilities = Facility::query()
            ->where('user_id', $user->id)
            ->orderBy('facility_name')
            ->get(['id', 'facility_name']);

        $recipients = Recipient::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('recipient_name')
            ->get(['id', 'recipient_name', 'facility_name']);

        return response()->json([
            'success' => true,
            'data' => [
                'facilities' => $facilities,
                'recipients' => $recipients,
            ],
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $userRole = $this->normalizeRole($user->role);
        $companyName = $this->normalizeCompanyName($user->company_name);
        $canReviewAll = $this->canReviewAll($request);

        $query = Announcement::query();
        $query->where(function ($q) use ($companyName, $user) {
            if ($companyName === '') {
                $q->where('announcements.user_id', $user->id);
                return;
            }

            $q->where('announcements.company_name', $companyName)
                ->orWhere(function ($fallback) use ($user, $companyName) {
                    // Backward compatibility for old rows before company scope migration.
                    $fallback->whereNull('announcements.company_name')
                        ->whereIn('announcements.user_id', User::query()
                            ->select('id')
                            ->whereRaw('LOWER(TRIM(company_name)) = ?', [$companyName])
                        );
                })
                ->orWhere(function ($fallback) use ($user) {
                    // Final fallback for users without company_name.
                    $fallback->whereNull('announcements.company_name')
                        ->where('announcements.user_id', $user->id);
                });
        });

        if (!$canReviewAll) {
            $query->where(function ($q) use ($user, $userRole) {
                $q->where('announcements.user_id', $user->id)
                    ->orWhere('announcements.audience', 'all');

                if ($userRole === 'staff') {
                    $q->orWhere('announcements.audience', 'staff');
                }

                if ($this->isAdminAudienceRole($userRole)) {
                    $q->orWhere('announcements.audience', 'admins');
                }

                if ($userRole !== '') {
                    $q->orWhereJsonContains('announcements.target_roles', $userRole);
                }
            });
        }

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', '%' . $term . '%')
                    ->orWhere('body', 'like', '%' . $term . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('announcements.status', $request->string('status')->toString());
        }

        if ($request->filled('priority')) {
            $query->where('announcements.priority', $request->string('priority')->toString());
        }

        if ($request->filled('audience')) {
            $query->where('announcements.audience', $request->string('audience')->toString());
        }

        if ($request->boolean('active')) {
            $now = Carbon::now();
            $query->where('announcements.status', 'published')
                ->where(function ($q) {
                    $q->whereNull('announcements.requires_approval')
                        ->orWhere('announcements.requires_approval', false)
                        ->orWhere('announcements.approval_status', 'approved');
                })
                ->where(function ($q) use ($now) {
                    $q->whereNull('announcements.publish_at')->orWhere('announcements.publish_at', '<=', $now);
                })
                ->where(function ($q) use ($now) {
                    $q->whereNull('announcements.expires_at')->orWhere('announcements.expires_at', '>=', $now);
                });
        }

        $query->leftJoin('announcement_reads as ar', function ($join) use ($user) {
            $join->on('announcements.id', '=', 'ar.announcement_id')
                ->where('ar.user_id', $user->id);
        });

        $items = $query
            ->select('announcements.*', 'ar.read_at as read_at')
            ->addSelect(['read_count' => AnnouncementRead::query()
                ->selectRaw('count(*)')
                ->whereColumn('announcement_id', 'announcements.id')
            ])
            ->withCount('attachments')
            ->orderByDesc('announcements.pinned')
            ->orderByDesc('announcements.occurred_at')
            ->orderByDesc('announcements.publish_at')
            ->orderByDesc('announcements.created_at')
            ->get()
            ->map(function ($item) {
                $item->is_read = (bool) $item->read_at;
                return $item;
            });

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function show(Request $request, Announcement $announcement)
    {
        if (!$this->canViewAnnouncement($request, $announcement)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $announcement->load('attachments');
        return response()->json(['success' => true, 'data' => $announcement]);
    }

    public function attachments(Request $request, Announcement $announcement)
    {
        if (!$this->canManageAnnouncement($request, $announcement)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $announcement->load('attachments');
        return response()->json(['success' => true, 'data' => $announcement->attachments]);
    }

    public function uploadAttachment(Request $request, Announcement $announcement)
    {
        if (!$this->canManageAnnouncement($request, $announcement)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $maxFileMb = (int) config('announcements.attachments.max_file_mb', 5);
        $allowedMimes = config('announcements.attachments.allowed_mimes', []);

        $validated = $request->validate([
            'files' => ['required', 'array'],
            'files.*' => [
                'file',
                'max:' . ($maxFileMb * 1024),
                $allowedMimes ? 'mimetypes:' . implode(',', $allowedMimes) : 'mimetypes:*/*',
            ],
        ]);

        $saved = [];
        foreach ($validated['files'] as $file) {
            $path = $file->store('announcement_attachments/' . $announcement->id);
            $saved[] = AnnouncementAttachment::create([
                'announcement_id' => $announcement->id,
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return response()->json(['success' => true, 'data' => $saved]);
    }

    public function downloadAttachment(Request $request, Announcement $announcement, AnnouncementAttachment $attachment)
    {
        if ($attachment->announcement_id !== $announcement->id) {
            abort(404);
        }
        if (!$this->canManageAnnouncement($request, $announcement) && !$this->canViewAnnouncement($request, $announcement)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return Storage::download($attachment->path, $attachment->original_name);
    }

    public function deleteAttachment(Request $request, Announcement $announcement, AnnouncementAttachment $attachment)
    {
        if ($attachment->announcement_id !== $announcement->id) {
            abort(404);
        }
        if (!$this->canManageAnnouncement($request, $announcement)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        Storage::delete($attachment->path);
        $attachment->delete();

        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $data = $this->normalizePayload($data, $request);

        if ($response = $this->validateRecipientSelection($data)) {
            return $response;
        }

        if ($response = $this->validateRequiredActions($data)) {
            return $response;
        }

        if ($data['audience'] === 'roles' && count($data['target_roles']) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Select at least one target role for role-based audience.',
                'errors' => [
                    'target_roles' => ['Select at least one target role for role-based audience.'],
                ],
            ], 422);
        }

        if ($data['status'] === 'published' && !$this->hasPermission($request, 'announcements', 'publish')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to publish announcements.',
            ], 403);
        }

        if (($data['requires_approval'] ?? false) && $data['status'] === 'published' && ($data['approval_status'] ?? 'pending') !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Approval required before publishing.',
                'errors' => [
                    'approval_status' => ['Approval required before publishing.'],
                ],
            ], 422);
        }

        if ($data['status'] === 'published' && empty($data['publish_at'])) {
            $data['publish_at'] = now();
        }

        $data['user_id'] = $request->user()->id;
        $announcement = Announcement::create($data);
        if (empty($announcement->announcement_no)) {
            $announcement->update([
                'announcement_no' => $this->buildAnnouncementNo($announcement),
            ]);
        }

        return response()->json(['success' => true, 'data' => $announcement->fresh()], 201);
    }

    public function update(Request $request, Announcement $announcement)
    {
        if (!$this->canManageAnnouncement($request, $announcement)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = $this->validatePayload($request, $announcement);
        $data = $this->normalizePayload($data, $request);

        if ($response = $this->validateRecipientSelection($data)) {
            return $response;
        }

        if ($response = $this->validateRequiredActions($data)) {
            return $response;
        }

        if ($data['audience'] === 'roles' && count($data['target_roles']) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Select at least one target role for role-based audience.',
                'errors' => [
                    'target_roles' => ['Select at least one target role for role-based audience.'],
                ],
            ], 422);
        }

        if ($data['status'] === 'published' && !$this->hasPermission($request, 'announcements', 'publish')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to publish announcements.',
            ], 403);
        }

        if (($data['requires_approval'] ?? false) && $data['status'] === 'published' && ($data['approval_status'] ?? 'pending') !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Approval required before publishing.',
                'errors' => [
                    'approval_status' => ['Approval required before publishing.'],
                ],
            ], 422);
        }

        if ($data['status'] === 'published' && empty($data['publish_at'])) {
            $data['publish_at'] = now();
        }

        $announcement->update($data);
        if (empty($announcement->announcement_no)) {
            $announcement->update([
                'announcement_no' => $this->buildAnnouncementNo($announcement),
            ]);
        }

        return response()->json(['success' => true, 'data' => $announcement->fresh()]);
    }

    public function destroy(Request $request, Announcement $announcement)
    {
        if (!$this->canManageAnnouncement($request, $announcement)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $announcement->delete();

        return response()->json(['success' => true]);
    }

    public function markRead(Request $request, Announcement $announcement)
    {
        if (!$this->canViewAnnouncement($request, $announcement)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $user = $request->user();

        AnnouncementRead::updateOrCreate(
            ['announcement_id' => $announcement->id, 'user_id' => $user->id],
            ['read_at' => now()]
        );

        return response()->json(['success' => true]);
    }

    public function publish(Request $request, Announcement $announcement)
    {
        if (!$this->canManageAnnouncement($request, $announcement)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($announcement->requires_approval && $announcement->approval_status !== 'approved') {
            return response()->json(['success' => false, 'message' => 'Approval required before publishing.'], 422);
        }

        $announcement->update([
            'status' => 'published',
            'publish_at' => $announcement->publish_at ?: now(),
        ]);

        return response()->json(['success' => true, 'data' => $announcement]);
    }

    public function archive(Request $request, Announcement $announcement)
    {
        if (!$this->canManageAnnouncement($request, $announcement)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $announcement->update(['status' => 'archived']);

        return response()->json(['success' => true, 'data' => $announcement]);
    }

    public function submit(Request $request, Announcement $announcement)
    {
        if (!$this->isSameCompany($request, $announcement)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($response = $this->denyUnlessOwns($request, $announcement)) {
            return $response;
        }

        $announcement->update([
            'requires_approval' => true,
            'approval_status' => 'pending',
        ]);

        return response()->json(['success' => true, 'data' => $announcement]);
    }

    public function approve(Request $request, Announcement $announcement)
    {
        if ($response = $this->denyUnlessPermitted($request, 'announcements', 'approve')) {
            return $response;
        }
        if (!$this->isSameCompany($request, $announcement)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $announcement->update([
            'requires_approval' => true,
            'approval_status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return response()->json(['success' => true, 'data' => $announcement]);
    }

    public function reject(Request $request, Announcement $announcement)
    {
        if ($response = $this->denyUnlessPermitted($request, 'announcements', 'approve')) {
            return $response;
        }
        if (!$this->isSameCompany($request, $announcement)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $announcement->update([
            'requires_approval' => true,
            'approval_status' => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return response()->json(['success' => true, 'data' => $announcement]);
    }

    private function canReviewAll(Request $request): bool
    {
        return $this->hasPermission($request, 'announcements', 'approve');
    }

    private function canManageAnnouncement(Request $request, Announcement $announcement): bool
    {
        if (!$this->isSameCompany($request, $announcement)) {
            return false;
        }

        if ($this->canReviewAll($request)) {
            return true;
        }

        $user = $request->user();
        if (!$user) {
            return false;
        }

        return (int) $announcement->user_id === (int) $user->id;
    }

    private function canViewAnnouncement(Request $request, Announcement $announcement): bool
    {
        if (!$this->isSameCompany($request, $announcement)) {
            return false;
        }

        if ($this->canManageAnnouncement($request, $announcement)) {
            return true;
        }

        if ($announcement->audience === 'all') {
            return true;
        }

        $user = $request->user();
        if (!$user) {
            return false;
        }

        $role = $this->normalizeRole($user->role);
        if ($role === '') {
            return false;
        }

        $audience = strtolower(trim((string) $announcement->audience));
        if ($audience === 'staff') {
            return $role === 'staff';
        }
        if ($audience === 'admins') {
            return $this->isAdminAudienceRole($role);
        }

        $targetRoles = $this->normalizeTargetRoles($announcement->target_roles ?? []);
        return in_array($role, $targetRoles, true);
    }

    private function validatePayload(Request $request, ?Announcement $announcement = null): array
    {
        $userId = (int) $request->user()->id;

        return $request->validate([
            'announcement_no' => [
                'nullable',
                'string',
                'max:60',
                Rule::unique('announcements', 'announcement_no')->ignore($announcement?->id),
            ],
            'occurred_at' => ['nullable', 'date'],
            'facility_id' => [
                'nullable',
                'integer',
                Rule::exists('facilities', 'id')->where(function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }),
            ],
            'recipient_mode' => ['nullable', Rule::in(['one', 'multiple', 'all'])],
            'recipient_ids' => ['nullable', 'array'],
            'recipient_ids.*' => [
                'integer',
                Rule::exists('recipients', 'id')->where(function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }),
            ],
            'required_actions' => ['nullable', 'array', 'max:20'],
            'required_actions.*.action' => ['nullable', 'string', 'max:255'],
            'required_actions.*.due_at' => ['nullable', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'audience' => ['required', Rule::in(['all', 'staff', 'admins', 'roles'])],
            'target_roles' => ['nullable', 'array', 'required_if:audience,roles'],
            'target_roles.*' => ['string', 'max:30'],
            'requires_approval' => ['nullable', 'boolean'],
            'approval_status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'publish_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:publish_at'],
            'pinned' => ['required', 'boolean'],
        ]);
    }

    private function normalizePayload(array $data, Request $request): array
    {
        $data['announcement_no'] = $this->normalizeAnnouncementNo($data['announcement_no'] ?? null);
        $data['occurred_at'] = $data['occurred_at'] ?? ($data['publish_at'] ?? now());

        $facilityId = isset($data['facility_id']) ? (int) $data['facility_id'] : null;
        if ($facilityId !== null && $facilityId <= 0) {
            $facilityId = null;
        }
        $data['facility_id'] = $facilityId;
        $data['facility_name'] = null;
        if ($facilityId !== null) {
            $facility = Facility::query()
                ->where('user_id', $request->user()->id)
                ->find($facilityId);
            if ($facility) {
                $data['facility_name'] = $facility->facility_name;
            } else {
                $data['facility_id'] = null;
            }
        }

        $recipientMode = strtolower(trim((string) ($data['recipient_mode'] ?? 'all')));
        if (!in_array($recipientMode, ['one', 'multiple', 'all'], true)) {
            $recipientMode = 'all';
        }

        $recipientIds = collect($data['recipient_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($recipientMode === 'all') {
            $recipientIds = collect();
        } elseif ($recipientMode === 'one') {
            $recipientIds = $recipientIds->take(1)->values();
        }

        $recipientRecords = collect();
        if ($recipientIds->isNotEmpty()) {
            $recipientRecords = Recipient::query()
                ->where('user_id', $request->user()->id)
                ->whereIn('id', $recipientIds->all())
                ->orderBy('recipient_name')
                ->get(['id', 'recipient_name']);

            $recipientIds = $recipientRecords
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values();

            if ($recipientMode === 'one') {
                $firstId = $recipientIds->first();
                $recipientIds = $firstId ? collect([(int) $firstId]) : collect();
                if ($firstId) {
                    $recipientRecords = $recipientRecords
                        ->where('id', (int) $firstId)
                        ->values();
                } else {
                    $recipientRecords = collect();
                }
            }
        }

        $data['recipient_mode'] = $recipientMode;
        $data['recipient_ids'] = $recipientIds->all();
        $data['recipient_labels'] = $recipientRecords->pluck('recipient_name')->values()->all();
        $data['required_actions'] = $this->normalizeRequiredActions($data['required_actions'] ?? []);

        $data['priority'] = strtolower(trim((string) ($data['priority'] ?? 'normal')));
        $data['status'] = strtolower(trim((string) ($data['status'] ?? 'draft')));
        $data['audience'] = strtolower(trim((string) ($data['audience'] ?? 'all')));
        $data['requires_approval'] = (bool) ($data['requires_approval'] ?? false);

        $targetRoles = $this->normalizeTargetRoles($data['target_roles'] ?? []);
        if ($data['audience'] === 'all') {
            $targetRoles = [];
        } elseif ($data['audience'] === 'staff') {
            $targetRoles = ['staff'];
        } elseif ($data['audience'] === 'admins') {
            $targetRoles = ['admin', 'manager'];
        }
        $data['target_roles'] = $targetRoles;

        if ($data['requires_approval']) {
            $approvalStatus = strtolower(trim((string) ($data['approval_status'] ?? 'pending')));
            $data['approval_status'] = in_array($approvalStatus, ['pending', 'approved', 'rejected'], true)
                ? $approvalStatus
                : 'pending';
        } else {
            $data['approval_status'] = null;
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }

        $data['company_name'] = $this->normalizeCompanyName($request->user()?->company_name) ?: null;
        return $data;
    }

    private function validateRecipientSelection(array $data)
    {
        $mode = $data['recipient_mode'] ?? 'all';
        $count = count($data['recipient_ids'] ?? []);

        if ($mode === 'one' && $count !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Recipient mode "One" requires exactly one recipient.',
                'errors' => [
                    'recipient_ids' => ['Recipient mode "One" requires exactly one recipient.'],
                ],
            ], 422);
        }

        if ($mode === 'multiple' && $count < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Recipient mode "Multiple" requires at least one recipient.',
                'errors' => [
                    'recipient_ids' => ['Recipient mode "Multiple" requires at least one recipient.'],
                ],
            ], 422);
        }

        return null;
    }

    private function validateRequiredActions(array $data)
    {
        $rows = $data['required_actions'] ?? [];
        if (!is_array($rows)) {
            return null;
        }

        foreach ($rows as $index => $row) {
            $action = trim((string) ($row['action'] ?? ''));
            $dueAt = trim((string) ($row['due_at'] ?? ''));

            if ($action === '' || $dueAt === '') {
                $rowNo = $index + 1;
                return response()->json([
                    'success' => false,
                    'message' => "Follow-up task row {$rowNo} must include both task description and due date-time.",
                    'errors' => [
                        "required_actions.{$index}" => ["Follow-up task row {$rowNo} must include both task description and due date-time."],
                    ],
                ], 422);
            }
        }

        return null;
    }

    private function normalizeRequiredActions(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return collect($value)
            ->filter(fn ($row) => is_array($row))
            ->map(function ($row) {
                $action = trim((string) ($row['action'] ?? ''));
                $dueAt = trim((string) ($row['due_at'] ?? ''));

                return [
                    'action' => $action,
                    'due_at' => $dueAt === '' ? null : Carbon::parse($dueAt)->toISOString(),
                ];
            })
            ->filter(fn ($row) => ($row['action'] ?? '') !== '' || !empty($row['due_at']))
            ->values()
            ->all();
    }

    private function normalizeAnnouncementNo(mixed $value): ?string
    {
        $normalized = strtoupper(trim((string) $value));
        return $normalized === '' ? null : $normalized;
    }

    private function buildAnnouncementNo(Announcement $announcement): string
    {
        $year = $announcement->created_at?->format('Y') ?? now()->format('Y');
        return sprintf('ANN-%s-%06d', $year, (int) $announcement->id);
    }

    private function normalizeTargetRoles(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(fn ($item) => strtolower(trim((string) $item)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeRole(mixed $value): string
    {
        return strtolower(trim((string) $value));
    }

    private function normalizeCompanyName(mixed $value): string
    {
        return strtolower(trim((string) $value));
    }

    private function isAdminAudienceRole(string $role): bool
    {
        return in_array($role, ['admin', 'manager'], true);
    }

    private function isSameCompany(Request $request, Announcement $announcement): bool
    {
        $user = $request->user();
        if (!$user) {
            return false;
        }

        $userCompany = $this->normalizeCompanyName($user->company_name);
        if ($userCompany === '') {
            return (int) $announcement->user_id === (int) $user->id;
        }

        $announcementCompany = $this->normalizeCompanyName($announcement->company_name);
        if ($announcementCompany !== '') {
            return $announcementCompany === $userCompany;
        }

        $announcement->loadMissing('user:id,company_name');
        $ownerCompany = $this->normalizeCompanyName($announcement->user?->company_name);
        if ($ownerCompany !== '') {
            return $ownerCompany === $userCompany;
        }

        return (int) $announcement->user_id === (int) $user->id;
    }
}
