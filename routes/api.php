<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AccountSetupController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\LockerController;
use App\Http\Controllers\Api\LockerAccessController;
use App\Http\Controllers\Api\CaptchaController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationLogController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\PlanHistoryController;
use App\Http\Controllers\Api\ParcelController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\UserAdminController;
use App\Http\Controllers\Api\TodoController;
use App\Http\Controllers\Api\FacilityController;
use App\Http\Controllers\Api\ParcelStorageController;
use App\Http\Controllers\Api\RecipientController;
use App\Http\Controllers\Api\CourierController;
use App\Http\Controllers\Api\DeliveryItemController;
use App\Http\Controllers\Api\DeliveryMethodController;
use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\SalesChatController;
use App\Http\Controllers\Api\DateTimeSettingsController;
use App\Http\Controllers\Api\ThemeSettingsController;
use App\Http\Controllers\Api\LanguageSettingsController;
use App\Http\Controllers\Api\UiTranslationController;
use App\Http\Controllers\Api\ParcelHoldingLimitsController;
use App\Http\Controllers\Api\NotificationSettingsController;
use App\Http\Controllers\Api\EmailMessageController;
use App\Http\Controllers\Api\EmailTemplateController;
use App\Http\Controllers\Api\EmailSettingsController;
use App\Http\Controllers\Api\EmailWebhookController;
use App\Http\Controllers\Api\EmailStatsController;
use App\Http\Controllers\Api\CalendarEventController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\WorkforceDailyReportController;
use App\Http\Controllers\Api\WorkforceIncidentReportController;
use App\Http\Controllers\Api\WorkforceTimesheetController;
use App\Http\Controllers\Api\WorkforceApprovalController;
use App\Http\Controllers\Api\WorkforceApprovalSettingsController;
use App\Http\Controllers\Api\PickupRuleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('email/webhooks/bounce', [EmailWebhookController::class, 'bounce']);
    Route::get('captcha', [CaptchaController::class, 'show']);
    Route::get('countries', [CountryController::class, 'index']);
    Route::post('contact', [ContactController::class, 'store']);
    Route::post('stripe/webhook', [StripeWebhookController::class, 'handle']);

    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('translations/ui', [UiTranslationController::class, 'translate'])->middleware('throttle:120,1');
    Route::post('verify', [AuthController::class, 'verify'])->middleware('throttle:10,1');
    Route::post('verify/resend', [AuthController::class, 'resendVerification'])->middleware('throttle:3,1');
    Route::post('forgot-password/request', [AuthController::class, 'forgotPasswordRequest'])->middleware('throttle:5,1');
    Route::post('forgot-password/reset', [AuthController::class, 'forgotPasswordReset'])->middleware('throttle:10,1');
    Route::post('forgot-username/request', [AuthController::class, 'forgotUsernameRequest'])->middleware('throttle:5,1');
    Route::get('verify/email/{id}/{hash}', [AuthController::class, 'verifyEmailLink'])
        ->name('api.verify.email')
        ->middleware('throttle:20,1');
    Route::post('sales-chat/reply', [SalesChatController::class, 'reply'])->middleware('throttle:12,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('verify/email/resend', [AuthController::class, 'resendEmailVerification'])->middleware('throttle:6,1');
        Route::get('account-setup', [AccountSetupController::class, 'show']);
        Route::post('account-setup', [AccountSetupController::class, 'store']);
        Route::post('account-setup/logo', [AccountSetupController::class, 'uploadLogo']);
        Route::post('account-setup/complete', [AccountSetupController::class, 'complete']);

        Route::get('dashboard', [DashboardController::class, 'summary']);
        Route::get('notifications', [NotificationLogController::class, 'index'])->middleware(['plan.feature:notification_center', 'permission:notifications,read']);
        Route::post('billing/checkout', [BillingController::class, 'checkout']);
        Route::post('billing/portal', [BillingController::class, 'portal']);
        Route::get('account/profile', [AccountController::class, 'profile'])->middleware('permission:account,read');
        Route::put('account/profile', [AccountController::class, 'updateProfile'])->middleware('permission:account,edit');
        Route::get('account/status', [AccountController::class, 'status'])->middleware('permission:account,read');
        Route::get('account/billing', [AccountController::class, 'billing'])->middleware('permission:account,read');
        Route::put('account/billing', [AccountController::class, 'updateBilling'])->middleware('permission:account,edit');
        Route::get('account/security', [AccountController::class, 'security'])->middleware('permission:account,read');
        Route::put('account/security', [AccountController::class, 'updateSecurity'])->middleware('permission:account,edit');
        Route::get('account/recovery', [AccountController::class, 'recovery'])->middleware('permission:account,read');
        Route::put('account/recovery', [AccountController::class, 'updateRecovery'])->middleware('permission:account,edit');
        Route::get('account/invoices', [AccountController::class, 'invoices'])->middleware('permission:account,read');
        Route::get('lockers', [LockerController::class, 'index'])->middleware(['plan.feature:storage_management', 'permission:parcels,read']);
        Route::get('lockers/{locker}', [LockerController::class, 'show'])->middleware(['plan.feature:storage_management', 'permission:parcels,read']);
        Route::post('lockers', [LockerController::class, 'store'])->middleware(['plan.feature:storage_management', 'permission:parcels,create']);
        Route::put('lockers/{locker}', [LockerController::class, 'update'])->middleware(['plan.feature:storage_management', 'permission:parcels,edit']);
        Route::delete('lockers/{locker}', [LockerController::class, 'destroy'])->middleware(['plan.feature:storage_management', 'permission:parcels,delete']);
        Route::get('locker-accesses', [LockerAccessController::class, 'index'])->middleware(['plan.feature:locker_access_management', 'permission:parcels,read']);
        Route::post('locker-accesses', [LockerAccessController::class, 'store'])->middleware(['plan.feature:locker_access_management', 'permission:parcels,create']);
        Route::put('locker-accesses/{lockerAccess}', [LockerAccessController::class, 'update'])->middleware(['plan.feature:locker_access_management', 'permission:parcels,edit']);
        Route::delete('locker-accesses/{lockerAccess}', [LockerAccessController::class, 'destroy'])->middleware(['plan.feature:locker_access_management', 'permission:parcels,delete']);
        Route::post('plans/select', [PlanController::class, 'select'])->middleware('permission:plans,edit');
        Route::get('plans/history', [PlanHistoryController::class, 'index'])->middleware('permission:plans,read');
        Route::get('parcels', [ParcelController::class, 'index'])->middleware('permission:parcels,read');
        Route::post('parcels', [ParcelController::class, 'store'])->middleware('permission:parcels,create');
        Route::get('parcels/{parcel}', [ParcelController::class, 'show'])->middleware('permission:parcels,read');
        Route::put('parcels/{parcel}', [ParcelController::class, 'update'])->middleware('permission:parcels,edit');
        Route::delete('parcels/{parcel}', [ParcelController::class, 'destroy'])->middleware('permission:parcels,delete');
        Route::post('parcels/scan', [ParcelController::class, 'scan'])->middleware('permission:parcels,edit');
        Route::get('parcels/{parcel}/history', [ParcelController::class, 'history'])->middleware('permission:parcels,read');

        Route::get('reports/parcels', [ReportController::class, 'exportParcels'])->middleware(['plan.feature:reporting', 'permission:reports,export']);

        Route::get('admin/users', [UserAdminController::class, 'index'])->middleware(['plan.feature:user_management', 'permission:users,read']);
        Route::post('admin/users', [UserAdminController::class, 'store'])->middleware(['plan.feature:user_management', 'permission:users,create']);
        Route::patch('admin/users/{user}', [UserAdminController::class, 'update'])->middleware(['plan.feature:user_management', 'permission:users,edit']);
        Route::get('admin/users/{user}/permissions', [UserAdminController::class, 'permissions'])->middleware(['plan.feature:user_management', 'permission:users,read']);
        Route::put('admin/users/{user}/permissions', [UserAdminController::class, 'updatePermissions'])->middleware(['plan.feature:user_management', 'permission:users,edit']);

        Route::get('todo', [TodoController::class, 'index']);
        Route::get('todo/assignees', [TodoController::class, 'assignees']);
        Route::post('todo', [TodoController::class, 'store']);
        Route::get('todo/{todo}', [TodoController::class, 'show']);
        Route::put('todo/{todo}', [TodoController::class, 'update']);
        Route::post('todo/{todo}/review', [TodoController::class, 'review']);
        Route::delete('todo/{todo}', [TodoController::class, 'destroy']);

        Route::get('facilities', [FacilityController::class, 'index'])->middleware(['plan.feature:profiles_core', 'permission:profiles,read']);
        Route::post('facilities', [FacilityController::class, 'store'])->middleware(['plan.feature:profiles_core', 'permission:profiles,create']);
        Route::get('facilities/{facility}', [FacilityController::class, 'show'])->middleware(['plan.feature:profiles_core', 'permission:profiles,read']);
        Route::put('facilities/{facility}', [FacilityController::class, 'update'])->middleware(['plan.feature:profiles_core', 'permission:profiles,edit']);
        Route::delete('facilities/{facility}', [FacilityController::class, 'destroy'])->middleware(['plan.feature:profiles_core', 'permission:profiles,delete']);

        Route::get('parcel-storages', [ParcelStorageController::class, 'index'])->middleware(['plan.feature:storage_management', 'permission:parcels,read']);
        Route::post('parcel-storages', [ParcelStorageController::class, 'store'])->middleware(['plan.feature:storage_management', 'permission:parcels,create']);
        Route::get('parcel-storages/{parcelStorage}', [ParcelStorageController::class, 'show'])->middleware(['plan.feature:storage_management', 'permission:parcels,read']);
        Route::put('parcel-storages/{parcelStorage}', [ParcelStorageController::class, 'update'])->middleware(['plan.feature:storage_management', 'permission:parcels,edit']);
        Route::delete('parcel-storages/{parcelStorage}', [ParcelStorageController::class, 'destroy'])->middleware(['plan.feature:storage_management', 'permission:parcels,delete']);

        Route::get('recipients', [RecipientController::class, 'index'])->middleware(['plan.feature:profiles_core', 'permission:profiles,read']);
        Route::post('recipients', [RecipientController::class, 'store'])->middleware(['plan.feature:profiles_core', 'permission:profiles,create']);
        Route::get('recipients/{recipient}', [RecipientController::class, 'show'])->middleware(['plan.feature:profiles_core', 'permission:profiles,read']);
        Route::put('recipients/{recipient}', [RecipientController::class, 'update'])->middleware(['plan.feature:profiles_core', 'permission:profiles,edit']);
        Route::delete('recipients/{recipient}', [RecipientController::class, 'destroy'])->middleware(['plan.feature:profiles_core', 'permission:profiles,delete']);

        Route::get('couriers', [CourierController::class, 'index'])->middleware(['plan.feature:profiles_core', 'permission:profiles,read']);
        Route::post('couriers', [CourierController::class, 'store'])->middleware(['plan.feature:profiles_core', 'permission:profiles,create']);
        Route::get('couriers/{courier}', [CourierController::class, 'show'])->middleware(['plan.feature:profiles_core', 'permission:profiles,read']);
        Route::put('couriers/{courier}', [CourierController::class, 'update'])->middleware(['plan.feature:profiles_core', 'permission:profiles,edit']);
        Route::delete('couriers/{courier}', [CourierController::class, 'destroy'])->middleware(['plan.feature:profiles_core', 'permission:profiles,delete']);

        Route::get('delivery-items', [DeliveryItemController::class, 'index'])->middleware(['plan.feature:profiles_core', 'permission:profiles,read']);
        Route::post('delivery-items', [DeliveryItemController::class, 'store'])->middleware(['plan.feature:profiles_core', 'permission:profiles,create']);
        Route::get('delivery-items/{deliveryItem}', [DeliveryItemController::class, 'show'])->middleware(['plan.feature:profiles_core', 'permission:profiles,read']);
        Route::put('delivery-items/{deliveryItem}', [DeliveryItemController::class, 'update'])->middleware(['plan.feature:profiles_core', 'permission:profiles,edit']);
        Route::delete('delivery-items/{deliveryItem}', [DeliveryItemController::class, 'destroy'])->middleware(['plan.feature:profiles_core', 'permission:profiles,delete']);

        Route::get('delivery-methods', [DeliveryMethodController::class, 'index'])->middleware(['plan.feature:profiles_core', 'permission:profiles,read']);
        Route::post('delivery-methods', [DeliveryMethodController::class, 'store'])->middleware(['plan.feature:profiles_core', 'permission:profiles,create']);
        Route::get('delivery-methods/{deliveryMethod}', [DeliveryMethodController::class, 'show'])->middleware(['plan.feature:profiles_core', 'permission:profiles,read']);
        Route::put('delivery-methods/{deliveryMethod}', [DeliveryMethodController::class, 'update'])->middleware(['plan.feature:profiles_core', 'permission:profiles,edit']);
        Route::delete('delivery-methods/{deliveryMethod}', [DeliveryMethodController::class, 'destroy'])->middleware(['plan.feature:profiles_core', 'permission:profiles,delete']);

        Route::get('sellers', [SellerController::class, 'index'])->middleware(['plan.feature:profiles_core', 'permission:profiles,read']);
        Route::post('sellers', [SellerController::class, 'store'])->middleware(['plan.feature:profiles_core', 'permission:profiles,create']);
        Route::get('sellers/{seller}', [SellerController::class, 'show'])->middleware(['plan.feature:profiles_core', 'permission:profiles,read']);
        Route::put('sellers/{seller}', [SellerController::class, 'update'])->middleware(['plan.feature:profiles_core', 'permission:profiles,edit']);
        Route::delete('sellers/{seller}', [SellerController::class, 'destroy'])->middleware(['plan.feature:profiles_core', 'permission:profiles,delete']);

        Route::get('settings/date-time', [DateTimeSettingsController::class, 'show'])->middleware('permission:settings,read');
        Route::put('settings/date-time', [DateTimeSettingsController::class, 'update'])->middleware('permission:settings,edit');
        Route::get('settings/theme', [ThemeSettingsController::class, 'show'])->middleware('permission:settings,read');
        Route::put('settings/theme', [ThemeSettingsController::class, 'update'])->middleware('permission:settings,edit');
        Route::get('settings/language', [LanguageSettingsController::class, 'show'])->middleware('permission:settings,read');
        Route::put('settings/language', [LanguageSettingsController::class, 'update'])->middleware('permission:settings,edit');
        Route::get('settings/holding-limits', [ParcelHoldingLimitsController::class, 'show'])->middleware('permission:settings,read');
        Route::put('settings/holding-limits', [ParcelHoldingLimitsController::class, 'update'])->middleware('permission:settings,edit');
        Route::get('settings/notifications', [NotificationSettingsController::class, 'show'])->middleware(['plan.feature:notification_center', 'permission:settings,read']);
        Route::put('settings/notifications', [NotificationSettingsController::class, 'update'])->middleware(['plan.feature:notification_center', 'permission:settings,edit']);

        Route::get('calendar-events', [CalendarEventController::class, 'index'])->middleware('permission:calendar,read');
        Route::post('calendar-events', [CalendarEventController::class, 'store'])->middleware('permission:calendar,create');
        Route::get('calendar-events/{event}', [CalendarEventController::class, 'show'])->middleware('permission:calendar,read');
        Route::put('calendar-events/{event}', [CalendarEventController::class, 'update'])->middleware('permission:calendar,edit');
        Route::delete('calendar-events/{event}', [CalendarEventController::class, 'destroy'])->middleware('permission:calendar,delete');
        Route::get('calendar-events/export', [CalendarEventController::class, 'export'])->middleware('permission:calendar,read');
        Route::post('calendar-events/import', [CalendarEventController::class, 'import'])->middleware('permission:calendar,create');

        Route::get('announcements', [AnnouncementController::class, 'index'])->middleware('permission:announcements,read');
        Route::get('announcements/meta', [AnnouncementController::class, 'meta'])->middleware('permission:announcements,read');
        Route::post('announcements', [AnnouncementController::class, 'store'])->middleware('permission:announcements,create');
        Route::get('announcements/{announcement}', [AnnouncementController::class, 'show'])->middleware('permission:announcements,read');
        Route::put('announcements/{announcement}', [AnnouncementController::class, 'update'])->middleware('permission:announcements,edit');
        Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy'])->middleware('permission:announcements,delete');
        Route::post('announcements/{announcement}/read', [AnnouncementController::class, 'markRead'])->middleware('permission:announcements,read');
        Route::post('announcements/{announcement}/publish', [AnnouncementController::class, 'publish'])->middleware('permission:announcements,publish');
        Route::post('announcements/{announcement}/archive', [AnnouncementController::class, 'archive'])->middleware('permission:announcements,publish');
        Route::post('announcements/{announcement}/submit', [AnnouncementController::class, 'submit'])->middleware('permission:announcements,edit');
        Route::post('announcements/{announcement}/approve', [AnnouncementController::class, 'approve'])->middleware('permission:announcements,approve');
        Route::post('announcements/{announcement}/reject', [AnnouncementController::class, 'reject'])->middleware('permission:announcements,approve');
        Route::get('announcements/{announcement}/attachments', [AnnouncementController::class, 'attachments'])->middleware('permission:announcements,read');
        Route::post('announcements/{announcement}/attachments', [AnnouncementController::class, 'uploadAttachment'])->middleware('permission:announcements,edit');
        Route::get('announcements/{announcement}/attachments/{attachment}', [AnnouncementController::class, 'downloadAttachment'])->middleware('permission:announcements,read');
        Route::delete('announcements/{announcement}/attachments/{attachment}', [AnnouncementController::class, 'deleteAttachment'])->middleware('permission:announcements,delete');

        Route::get('email/messages', [EmailMessageController::class, 'index'])->middleware('permission:email,read');
        Route::post('email/messages', [EmailMessageController::class, 'store'])->middleware('permission:email,create');
        Route::get('email/messages/{message}', [EmailMessageController::class, 'show'])->middleware('permission:email,read');
        Route::put('email/messages/{message}', [EmailMessageController::class, 'update'])->middleware('permission:email,edit');
        Route::post('email/messages/{message}/send', [EmailMessageController::class, 'send'])->middleware('permission:email,send');
        Route::post('email/messages/{message}/trash', [EmailMessageController::class, 'trash'])->middleware('permission:email,edit');
        Route::post('email/messages/{message}/restore', [EmailMessageController::class, 'restore'])->middleware('permission:email,edit');
        Route::delete('email/messages/{message}', [EmailMessageController::class, 'destroy'])->middleware('permission:email,delete');
        Route::post('email/messages/{message}/attachments', [EmailMessageController::class, 'uploadAttachment'])->middleware('permission:email,edit');
        Route::delete('email/messages/{message}/attachments/{attachment}', [EmailMessageController::class, 'deleteAttachment'])->middleware('permission:email,delete');
        Route::get('email/messages/{message}/attachments/{attachment}', [EmailMessageController::class, 'downloadAttachment'])->middleware('permission:email,read');
        Route::post('email/messages/sync', [EmailMessageController::class, 'sync'])->middleware('permission:email,read');
        Route::get('email/threads/{threadId}', [EmailMessageController::class, 'thread'])->middleware('permission:email,read');
        Route::post('email/threads/{threadId}/mark', [EmailMessageController::class, 'markThread'])->middleware('permission:email,edit');

        Route::get('email/templates', [EmailTemplateController::class, 'index'])->middleware('permission:email,read');
        Route::post('email/templates', [EmailTemplateController::class, 'store'])->middleware('permission:email,create');
        Route::get('email/templates/{template}', [EmailTemplateController::class, 'show'])->middleware('permission:email,read');
        Route::put('email/templates/{template}', [EmailTemplateController::class, 'update'])->middleware('permission:email,edit');
        Route::delete('email/templates/{template}', [EmailTemplateController::class, 'destroy'])->middleware('permission:email,delete');

        Route::get('email/settings', [EmailSettingsController::class, 'show'])->middleware('permission:email,read');
        Route::put('email/settings', [EmailSettingsController::class, 'update'])->middleware('permission:email,edit');
        Route::get('email/stats', [EmailStatsController::class, 'show'])->middleware('permission:email,read');

        Route::get('workforce/daily-reports', [WorkforceDailyReportController::class, 'index'])->middleware(['plan.feature:workforce_management', 'permission:workforce,read']);
        Route::post('workforce/daily-reports', [WorkforceDailyReportController::class, 'store'])->middleware(['plan.feature:workforce_management', 'permission:workforce,create']);
        Route::get('workforce/daily-reports/{report}', [WorkforceDailyReportController::class, 'show'])->middleware(['plan.feature:workforce_management', 'permission:workforce,read']);
        Route::put('workforce/daily-reports/{report}', [WorkforceDailyReportController::class, 'update'])->middleware(['plan.feature:workforce_management', 'permission:workforce,edit']);
        Route::delete('workforce/daily-reports/{report}', [WorkforceDailyReportController::class, 'destroy'])->middleware(['plan.feature:workforce_management', 'permission:workforce,edit']);
        Route::post('workforce/daily-reports/{report}/submit', [WorkforceDailyReportController::class, 'submit'])->middleware(['plan.feature:workforce_management', 'permission:workforce,edit']);
        Route::post('workforce/daily-reports/{report}/approve', [WorkforceDailyReportController::class, 'approve'])->middleware(['plan.feature:workforce_management', 'permission:workforce,approve']);
        Route::post('workforce/daily-reports/{report}/finalize', [WorkforceDailyReportController::class, 'finalize'])->middleware(['plan.feature:workforce_management', 'permission:workforce,approve']);
        Route::post('workforce/daily-reports/{report}/reject', [WorkforceDailyReportController::class, 'reject'])->middleware(['plan.feature:workforce_management', 'permission:workforce,approve']);

        Route::get('workforce/incident-reports', [WorkforceIncidentReportController::class, 'index'])->middleware(['plan.feature:workforce_management', 'permission:workforce,read']);
        Route::post('workforce/incident-reports', [WorkforceIncidentReportController::class, 'store'])->middleware(['plan.feature:workforce_management', 'permission:workforce,create']);
        Route::get('workforce/incident-reports/{incident}', [WorkforceIncidentReportController::class, 'show'])->middleware(['plan.feature:workforce_management', 'permission:workforce,read']);
        Route::put('workforce/incident-reports/{incident}', [WorkforceIncidentReportController::class, 'update'])->middleware(['plan.feature:workforce_management', 'permission:workforce,edit']);
        Route::delete('workforce/incident-reports/{incident}', [WorkforceIncidentReportController::class, 'destroy'])->middleware(['plan.feature:workforce_management', 'permission:workforce,edit']);
        Route::post('workforce/incident-reports/{incident}/submit', [WorkforceIncidentReportController::class, 'submit'])->middleware(['plan.feature:workforce_management', 'permission:workforce,edit']);
        Route::post('workforce/incident-reports/{incident}/approve', [WorkforceIncidentReportController::class, 'approve'])->middleware(['plan.feature:workforce_management', 'permission:workforce,approve']);
        Route::post('workforce/incident-reports/{incident}/finalize', [WorkforceIncidentReportController::class, 'finalize'])->middleware(['plan.feature:workforce_management', 'permission:workforce,approve']);
        Route::post('workforce/incident-reports/{incident}/reject', [WorkforceIncidentReportController::class, 'reject'])->middleware(['plan.feature:workforce_management', 'permission:workforce,approve']);

        Route::get('workforce/timesheets', [WorkforceTimesheetController::class, 'index'])->middleware(['plan.feature:workforce_management', 'permission:workforce,read']);
        Route::post('workforce/timesheets', [WorkforceTimesheetController::class, 'store'])->middleware(['plan.feature:workforce_management', 'permission:workforce,create']);
        Route::get('workforce/timesheets/{timesheet}', [WorkforceTimesheetController::class, 'show'])->middleware(['plan.feature:workforce_management', 'permission:workforce,read']);
        Route::put('workforce/timesheets/{timesheet}', [WorkforceTimesheetController::class, 'update'])->middleware(['plan.feature:workforce_management', 'permission:workforce,edit']);
        Route::delete('workforce/timesheets/{timesheet}', [WorkforceTimesheetController::class, 'destroy'])->middleware(['plan.feature:workforce_management', 'permission:workforce,edit']);
        Route::post('workforce/timesheets/{timesheet}/submit', [WorkforceTimesheetController::class, 'submit'])->middleware(['plan.feature:workforce_management', 'permission:workforce,edit']);
        Route::post('workforce/timesheets/{timesheet}/approve', [WorkforceTimesheetController::class, 'approve'])->middleware(['plan.feature:workforce_management', 'permission:workforce,approve']);
        Route::post('workforce/timesheets/{timesheet}/finalize', [WorkforceTimesheetController::class, 'finalize'])->middleware(['plan.feature:workforce_management', 'permission:workforce,approve']);
        Route::post('workforce/timesheets/{timesheet}/reject', [WorkforceTimesheetController::class, 'reject'])->middleware(['plan.feature:workforce_management', 'permission:workforce,approve']);

        Route::get('workforce/approvals', [WorkforceApprovalController::class, 'index'])->middleware(['plan.feature:workforce_management', 'permission:workforce,read']);
        Route::get('workforce/approvals/sla', [WorkforceApprovalController::class, 'slaDashboard'])->middleware(['plan.feature:workforce_management', 'permission:workforce,read']);
        Route::get('workforce/approvals/sla/export', [WorkforceApprovalController::class, 'exportSla'])->middleware(['plan.feature:workforce_management', 'permission:workforce,export']);
        Route::get('workforce/approvals/export', [WorkforceApprovalController::class, 'export'])->middleware(['plan.feature:workforce_management', 'permission:workforce,export']);
        Route::get('settings/approvals', [WorkforceApprovalSettingsController::class, 'show'])->middleware(['plan.feature:workforce_management', 'permission:settings,read']);
        Route::put('settings/approvals', [WorkforceApprovalSettingsController::class, 'update'])->middleware(['plan.feature:workforce_management', 'permission:settings,edit']);

        Route::get('pickup-rules', [PickupRuleController::class, 'index'])->middleware(['plan.feature:pickup_management', 'permission:pickup,read']);
        Route::post('pickup-rules', [PickupRuleController::class, 'store'])->middleware(['plan.feature:pickup_management', 'permission:pickup,create']);
        Route::get('pickup-rules/{pickupRule}/audits', [PickupRuleController::class, 'audits'])->middleware(['plan.feature:pickup_management', 'permission:pickup,read']);
        Route::put('pickup-rules/{pickupRule}', [PickupRuleController::class, 'update'])->middleware(['plan.feature:pickup_management', 'permission:pickup,edit']);
        Route::delete('pickup-rules/{pickupRule}', [PickupRuleController::class, 'destroy'])->middleware(['plan.feature:pickup_management', 'permission:pickup,delete']);
        Route::get('pickup-rules/sla/summary', [PickupRuleController::class, 'slaSummary'])->middleware(['plan.feature:reporting', 'permission:pickup,read']);
        Route::get('pickup-rules/sla/export', [PickupRuleController::class, 'exportSla'])->middleware(['plan.feature:reporting', 'permission:pickup,export']);
    });
});
