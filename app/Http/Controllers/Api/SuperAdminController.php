<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BillingInvoice;
use App\Models\AccountSetup;
use App\Models\AccountLoginHistory;
use App\Models\SupportTicket;
use App\Models\SupportTicketActivity;
use App\Models\CalendarEvent;
use App\Models\Announcement;
use App\Models\WebsiteVisit;
use App\Models\EmergencyShutdown;
use App\Models\ExternalAccessAttempt;
use App\Models\NotificationLog;
use App\Models\PlanChangeLog;
use App\Models\AccountSecurity;
use App\Models\AccountSecurityDevice;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\StripeBillingService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function accountStatusReport(){ $users=User::where('role','!=','super_admin')->get();$rows=$users->map(function($u){$latest=BillingInvoice::where('user_id',$u->id)->latest()->first();return ['customer_no'=>'CUST-'.str_pad($u->id,6,'0',STR_PAD_LEFT),'company'=>$u->company_name?:$u->name,'status'=>$u->is_active?'Active':'Inactive','plan'=>ucfirst($u->selected_plan?:'Not selected'),'since'=>$u->created_at?->toDateString(),'last_activity'=>$u->updated_at?->toDateString(),'balance'=>round(($latest?->amount_remaining??0)/100,2),'country'=>$u->country,'payment_status'=>ucfirst($latest?->status?:'No invoice')];});return response()->json(['success'=>true,'data'=>['rows'=>$rows,'summary'=>['total'=>$rows->count(),'active'=>$rows->where('status','Active')->count(),'inactive'=>$rows->where('status','Inactive')->count(),'past_due'=>$rows->filter(fn($r)=>($r['balance']??0)>0)->count()]]]);}
    public function loginHistoryReport(){ $logs=AccountLoginHistory::latest('logged_in_at')->limit(500)->get();$users=User::whereIn('id',$logs->pluck('user_id'))->get()->keyBy('id');$rows=$logs->map(function($l)use($users){$u=$users->get($l->user_id);return ['date'=>$l->logged_in_at?->toDateString(),'time'=>$l->logged_in_at?->format('h:i:s A'),'customer_no'=>'CUST-'.str_pad($l->user_id,6,'0',STR_PAD_LEFT),'company'=>$u?->company_name?:'—','user'=>$u?->name?:'—','role'=>$u?->role?:'—','event'=>'Login','status'=>$l->result,'ip'=>$l->ip_address,'device'=>trim(($l->device_name?:'').' '.($l->browser?:'')),'country'=>$l->location];});return response()->json(['success'=>true,'data'=>['rows'=>$rows,'summary'=>['total'=>$rows->count(),'successful'=>$rows->where('status','Successful')->count(),'failed'=>ExternalAccessAttempt::where('result','Failed')->count(),'active_sessions'=>0]]]);}
    public function usersMonitoringReport(){ $users=User::where('role','!=','super_admin')->get();$logs=AccountLoginHistory::latest('logged_in_at')->limit(500)->get();$rows=$logs->map(function($l)use($users){$u=$users->firstWhere('id',$l->user_id);return ['date'=>$l->logged_in_at?->toDateString(),'time'=>$l->logged_in_at?->format('h:i:s A'),'customer_no'=>'CUST-'.str_pad($l->user_id,6,'0',STR_PAD_LEFT),'company'=>$u?->company_name?:'—','user'=>$u?->name?:'—','role'=>$u?->role?:'—','activity'=>'Login','user_status'=>$u?->is_active?'Active':'Inactive','risk'=>'Low','ip'=>$l->ip_address,'device'=>trim(($l->device_name?:'').' '.($l->browser?:'')),'country'=>$l->location];});return response()->json(['success'=>true,'data'=>['rows'=>$rows,'summary'=>['total_users'=>$users->count(),'active_users'=>$users->where('is_active',true)->count(),'suspicious'=>0,'failed_logins'=>ExternalAccessAttempt::where('result','Failed')->count()]]]);}
    public function notifications(Request $request){$v=$request->validate(['search'=>'nullable|string|max:100','status'=>'nullable|string|max:40','channel'=>'nullable|string|max:40']);$items=NotificationLog::query()->latest();if($v['search']??null)$items->where(fn($q)=>$q->where('context','like','%'.$v['search'].'%')->orWhere('to','like','%'.$v['search'].'%'));if($v['status']??null)$items->where('status',$v['status']);if($v['channel']??null)$items->where('channel',$v['channel']);$items=$items->limit(200)->get();$users=User::whereIn('id',$items->pluck('user_id')->filter())->get()->keyBy('id');$rows=$items->map(function($item)use($users){$meta=is_array($item->context_json)?$item->context_json:(json_decode($item->context_json?:'{}',true)?:[]);return ['id'=>$item->id,'notification_no'=>'NTF-'.str_pad((string)$item->id,8,'0',STR_PAD_LEFT),'date'=>$item->created_at?->toIso8601String(),'customer'=>$users->get($item->user_id)?->company_name?:'System','type'=>$meta['type']??'System notification','channel'=>ucfirst($item->channel),'recipient'=>$item->to,'subject'=>$meta['subject']??$item->context,'recurring'=>$meta['recurring']??'One-time','expires_at'=>$meta['expires_at']??null,'status'=>ucfirst($item->status),'error'=>$item->error];});return response()->json(['success'=>true,'data'=>['summary'=>['total'=>$rows->count(),'scheduled'=>$rows->where('status','Scheduled')->count(),'sent'=>$rows->where('status','Sent')->count(),'expired'=>$rows->filter(fn($r)=>$r['expires_at']&&Carbon::parse($r['expires_at'])->isPast())->count(),'failed'=>$rows->where('status','Failed')->count()],'rows'=>$rows]]);}
    public function createNotification(Request $request){$d=$request->validate(['user_id'=>'nullable|exists:users,id','channel'=>'required|in:email,sms','recipient'=>'required|string|max:255','type'=>'required|string|max:80','subject'=>'required|string|max:255','recurring'=>'nullable|string|max:80','expires_at'=>'nullable|date','schedule_for'=>'nullable|date']);$status=isset($d['schedule_for'])&&Carbon::parse($d['schedule_for'])->isFuture()?'scheduled':'sent';$log=NotificationLog::create(['user_id'=>$d['user_id']??null,'channel'=>$d['channel'],'status'=>$status,'to'=>$d['recipient'],'context'=>$d['subject'],'context_json'=>['type'=>$d['type'],'subject'=>$d['subject'],'recurring'=>$d['recurring']??'One-time','expires_at'=>$d['expires_at']??null]]);return response()->json(['success'=>true,'data'=>$log],201);}
    public function importPaymentGateway(Request $request, StripeBillingService $billing){if(!config('services.stripe.secret'))return response()->json(['message'=>'Stripe is not configured. Add the Stripe secret before importing payments.'],422);try{$stripe=new \Stripe\StripeClient(config('services.stripe.secret'));$items=$stripe->invoices->all(['limit'=>100])->data;$users=User::whereNotNull('stripe_customer_id')->get()->keyBy('stripe_customer_id');$count=0;foreach($items as $invoice){$user=$users->get($invoice->customer??'');if(!$user)continue;$tax=collect($invoice->total_tax_amounts??[])->sum(fn($x)=>(int)($x->amount??0));$billing->recordInvoice($user,['stripe_invoice_id'=>$invoice->id,'stripe_subscription_id'=>$invoice->subscription??null,'status'=>$invoice->status??null,'currency'=>$invoice->currency??null,'amount_due'=>$invoice->amount_due??null,'amount_paid'=>$invoice->amount_paid??null,'amount_remaining'=>$invoice->amount_remaining??null,'plan_name'=>data_get($invoice,'lines.data.0.description'),'tax_amount'=>$tax?:null,'period_start'=>isset($invoice->period_start)?now()->setTimestamp($invoice->period_start):null,'period_end'=>isset($invoice->period_end)?now()->setTimestamp($invoice->period_end):null,'hosted_invoice_url'=>$invoice->hosted_invoice_url??null,'invoice_pdf'=>$invoice->invoice_pdf??null]);$count++;}return response()->json(['success'=>true,'data'=>['imported'=>$count,'retrieved'=>count($items),'gateway'=>'Stripe']]);}catch(\Throwable $e){return response()->json(['message'=>'Payment gateway import failed: '.$e->getMessage()],422);}}
    public function systemMonitoring(){ $database='Healthy';try{DB::select('select 1');}catch(\Throwable $e){$database='Unavailable';}$cache='Healthy';try{Cache::put('super_admin_health_check',now()->timestamp,30);Cache::get('super_admin_health_check');}catch(\Throwable $e){$cache='Unavailable';}$storage=storage_path();$free=@disk_free_space($storage);$total=@disk_total_space($storage);return response()->json(['success'=>true,'data'=>['checked_at'=>now()->toIso8601String(),'services'=>[['name'=>'Application','status'=>'Healthy','detail'=>'Laravel application is responding'],['name'=>'Database','status'=>$database,'detail'=>DB::connection()->getDriverName().' connection'],['name'=>'Cache','status'=>$cache,'detail'=>'Application cache health check'],['name'=>'Storage','status'=>is_writable($storage)?'Healthy':'Attention','detail'=>$free!==false&&$total?round(($free/$total)*100).'% free space':'Storage capacity unavailable']], 'activity'=>['paid_invoices_today'=>BillingInvoice::where('status','paid')->whereDate('created_at',today())->count(),'failed_logins_today'=>ExternalAccessAttempt::where('result','Failed')->whereDate('created_at',today())->count(),'active_shutdowns'=>EmergencyShutdown::where('status','active')->count()]]]);}
    public function externalSecurity(Request $request){$items=ExternalAccessAttempt::latest()->limit(200)->get();return response()->json(['success'=>true,'data'=>['rows'=>$items,'summary'=>['total'=>$items->count(),'failed'=>$items->where('result','Failed')->count(),'blocked'=>$items->where('result','Blocked')->count(),'unique_ips'=>$items->pluck('ip_address')->filter()->unique()->count(),'high_risk'=>$items->whereIn('threat_level',['high','critical'])->count()]]]);}
    public function shutdowns(Request $request)
    {
        $filters = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'customer' => 'nullable|string|max:100',
            'reason' => 'nullable|string|max:120',
            'scope' => 'nullable|string|max:60',
            'status' => 'nullable|in:active,completed',
        ]);

        $items = EmergencyShutdown::query()
            ->when($filters['from_date'] ?? null, fn ($query, $date) => $query->whereDate('starts_at', '>=', $date))
            ->when($filters['to_date'] ?? null, fn ($query, $date) => $query->whereDate('starts_at', '<=', $date))
            ->when($filters['customer'] ?? null, function ($query, $customer) {
                $id = preg_replace('/\D+/', '', $customer);
                if ($id !== '') $query->where('customer_id', $id);
            })
            ->when($filters['reason'] ?? null, fn ($query, $reason) => $query->where('reason', $reason))
            ->when($filters['scope'] ?? null, fn ($query, $scope) => $query->where('scope', $scope))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest('starts_at')
            ->paginate(10);

        return response()->json(['success' => true, 'data' => $items]);
    }
    public function createShutdown(Request $r){$d=$r->validate(['scope'=>'required|in:one_customer,selected_customers,all_customers,entire_platform','customer_id'=>'nullable|exists:users,id','reason'=>'required|string|max:120','risk_level'=>'required|in:low,medium,high,critical','expected_restore_at'=>'nullable|date','message'=>'nullable|string|max:5000','password'=>'required|string']);if(!Hash::check($d['password'],$r->user()->password))return response()->json(['message'=>'Administrator password is incorrect.'],422);$item=EmergencyShutdown::create(['scope'=>$d['scope'],'customer_id'=>$d['customer_id']??null,'reason'=>$d['reason'],'risk_level'=>$d['risk_level'],'starts_at'=>now(),'expected_restore_at'=>$d['expected_restore_at']??null,'message'=>$d['message']??null,'status'=>'active','requested_by'=>$r->user()->id,'approved_by'=>$r->user()->id]);return response()->json(['success'=>true,'data'=>$item],201);}
    public function restoreShutdown(Request $r,EmergencyShutdown $shutdown){$d=$r->validate(['password'=>'required|string']);if(!Hash::check($d['password'],$r->user()->password))return response()->json(['message'=>'Administrator password is incorrect.'],422);$shutdown->update(['status'=>'completed','restored_at'=>now(),'restored_by'=>$r->user()->id]);return response()->json(['success'=>true,'data'=>$shutdown]);}
    public function websiteVisitors(Request $request){
        $v=$request->validate(['from_date'=>'nullable|date','to_date'=>'nullable|date|after_or_equal:from_date','sign_up'=>'nullable|in:all,yes,no','location'=>'nullable|string|max:100','per_page'=>'nullable|integer|min:10|max:50']);
        $from=isset($v['from_date'])?Carbon::parse($v['from_date'])->startOfDay():now()->startOfMonth();$to=isset($v['to_date'])?Carbon::parse($v['to_date'])->endOfDay():now()->endOfDay();
        $visits=WebsiteVisit::query()->whereBetween('created_at',[$from,$to])->orderBy('created_at')->get();
        $rows=$visits->groupBy('visitor_key')->map(function($items,$key){$first=$items->first();$last=$items->last();$agent=(string)($last->user_agent?:$first->user_agent);$browser=str_contains($agent,'Edg/')?'Edge':(str_contains($agent,'Firefox/')?'Firefox':(str_contains($agent,'Chrome/')?'Chrome':(str_contains($agent,'Safari/')?'Safari':'Unknown')));$device=preg_match('/iPhone|Android.*Mobile|Windows Phone/i',$agent)?'Mobile':(preg_match('/iPad|Tablet/i',$agent)?'Tablet':'Desktop');$pages=$items->pluck('path')->filter()->unique()->values();return ['visitor_key'=>$key,'ip_address'=>$last->ip_address?:$first->ip_address,'visit_count'=>$items->count(),'spent_time'=>'—','location'=>'Unknown','signed_up'=>false,'sign_up_date'=>null,'first_visit'=>$first->created_at?->toIso8601String(),'last_visit'=>$last->created_at?->toIso8601String(),'device'=>$device,'browser'=>$browser,'pages_viewed'=>$pages->count(),'pages'=>$pages,'referral_source'=>$first->referrer?:'Direct'];})->values();
        if(($v['sign_up']??'all')==='yes')$rows=$rows->where('signed_up',true)->values();if(($v['sign_up']??'all')==='no')$rows=$rows->where('signed_up',false)->values();if(($v['location']??'all')!=='all')$rows=$rows->where('location',$v['location'])->values();
        $perPage=$v['per_page']??10;$page=max(1,(int)$request->input('page',1));$total=$rows->count();$lastPage=max(1,(int)ceil($total/$perPage));$page=min($page,$lastPage);$pageRows=$rows->slice(($page-1)*$perPage,$perPage)->values();$signedUp=User::where('role','!=','super_admin')->whereBetween('created_at',[$from,$to])->count();
        return response()->json(['success'=>true,'data'=>['summary'=>['total_visitors'=>$total,'signed_up'=>$signedUp,'signup_rate'=>$total?round(($signedUp/$total)*100):0,'average_spent_time'=>'00:00:00'],'locations'=>[],'rows'=>['data'=>$pageRows,'total'=>$total,'per_page'=>$perPage,'current_page'=>$page,'last_page'=>$lastPage,'prev_page_url'=>$page>1?'#':null,'next_page_url'=>$page<$lastPage?'#':null]]]);
    }
    public function calendar(Request $request)
    {
        return response()->json(['success' => true, 'data' => CalendarEvent::query()->with('user:id,name,company_name')->orderBy('start_at')->limit(200)->get()->map(fn (CalendarEvent $event) => ['event_no' => $event->event_no, 'title' => $event->title, 'start_at' => optional($event->start_at)->toIso8601String(), 'end_at' => optional($event->end_at)->toIso8601String(), 'type' => $event->event_type ?: 'General', 'priority' => $event->priority ?: 'medium', 'status' => $event->status ?: 'scheduled', 'created_by' => $event->user?->name ?: 'System'] )]);
    }

    public function storeCalendarEvent(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'all_day' => ['required', 'boolean'],
            'status' => ['nullable', 'string', 'max:30'],
            'visibility' => ['nullable', 'string', 'max:30'],
            'event_type' => ['nullable', 'string', 'max:50'],
            'priority' => ['nullable', 'in:low,medium,high,critical'],
            'recurrence_freq' => ['nullable', 'in:daily,weekly,monthly'],
            'recurrence_mode' => ['nullable', 'string', 'max:20'],
        ]);

        $data['user_id'] = $request->user()->id;
        $event = CalendarEvent::create($data);

        return response()->json(['success' => true, 'data' => $event], 201);
    }

    public function announcements(Request $request)
    {
        return response()->json(['success' => true, 'data' => Announcement::query()->with('user:id,name,company_name,phone,position')->latest()->limit(200)->get()->map(fn (Announcement $item) => ['id' => $item->id, 'announcement_no' => $item->announcement_no, 'title' => $item->title, 'body' => $item->body, 'priority' => $item->priority, 'status' => $item->status, 'audience' => $item->audience, 'publish_at' => optional($item->publish_at ?: $item->occurred_at ?: $item->created_at)->toIso8601String(), 'created_by' => $item->user?->name ?: 'System', 'admin_name' => $item->user?->name ?: 'Super Admin', 'phone' => $item->user?->phone, 'position' => $item->user?->position ?: 'System Admin'])]);
    }

    public function storeAnnouncement(Request $request)
    {
        $data = $request->validate([
            'announcement_no' => ['nullable', 'string', 'max:60'],
            'occurred_at' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'audience' => ['required', 'in:all,admins'],
            'status' => ['required', 'in:draft,published'],
            'in_app' => ['nullable', 'boolean'],
            'email' => ['nullable', 'boolean'],
        ]);
        $item = Announcement::create([
            'user_id' => $request->user()->id,
            'announcement_no' => $data['announcement_no'] ?: null,
            'occurred_at' => $data['occurred_at'],
            'title' => $data['title'],
            'body' => $data['body'],
            'priority' => 'normal',
            'status' => $data['status'],
            'audience' => $data['audience'],
            'recipient_mode' => 'all',
            'target_roles' => [],
            'requires_approval' => false,
            'pinned' => false,
            'publish_at' => $data['status'] === 'published' ? now() : null,
        ]);
        if (!$item->announcement_no) $item->update(['announcement_no' => 'AN-' . str_pad((string) $item->id, 4, '0', STR_PAD_LEFT)]);
        return response()->json(['success' => true, 'data' => $item->fresh()], 201);
    }
    public function pendingAccounts(Request $request)
    {
        $v=$request->validate(['from_date'=>'nullable|date','to_date'=>'nullable|date|after_or_equal:from_date','plan'=>'nullable|string|max:100','payment_status'=>'nullable|in:all,paid,unpaid','search'=>'nullable|string|max:100','per_page'=>'nullable|integer|min:10|max:50']);
        $query=User::query()->where('role','!=','super_admin')->whereNull('account_setup_completed_at');
        if($v['from_date']??null)$query->whereDate('created_at','>=',$v['from_date']);if($v['to_date']??null)$query->whereDate('created_at','<=',$v['to_date']);if(($v['plan']??'all')!=='all')$query->where('selected_plan',$v['plan']);
        if($search=trim((string)($v['search']??'')))$query->where(fn($q)=>$q->where('name','like',"%{$search}%")->orWhere('company_name','like',"%{$search}%")->orWhere('email','like',"%{$search}%")->orWhere('phone','like',"%{$search}%")->orWhere('id',$search));
        $accounts=$query->latest()->paginate($v['per_page']??10);
        $accounts->setCollection($accounts->getCollection()->map(function(User $user)use($v){$invoice=BillingInvoice::where('user_id',$user->id)->latest()->first();$paid=(int)($invoice?->amount_paid??0);$status=$paid>0?'paid':'unpaid';return ['id'=>$user->id,'is_new'=>$user->created_at?->gte(now()->subDays(30))??false,'sign_up_date'=>$user->created_at?->toIso8601String(),'account_number'=>'ACC-'.($user->created_at?->format('Y')?:now()->format('Y')).'-'.str_pad((string)$user->id,5,'0',STR_PAD_LEFT),'customer'=>$user->company_name?:$user->name,'phone'=>$user->phone,'email'=>$user->email,'plan'=>ucfirst($user->selected_plan?:'Not selected'),'plan_key'=>$user->selected_plan?:'Not selected','paid_now'=>round($paid/100,2),'payment_status'=>$status,'status'=>'Under Review'];})->when(($v['payment_status']??'all')!=='all',fn($rows)=>$rows->where('payment_status',$v['payment_status'])->values()));
        return response()->json(['success'=>true,'data'=>['accounts'=>$accounts,'plans'=>User::query()->where('role','!=','super_admin')->whereNotNull('selected_plan')->distinct()->orderBy('selected_plan')->pluck('selected_plan')->map(fn($plan)=>ucfirst($plan))->values(),'last_updated'=>now()->toIso8601String()]]);
    }
    public function approvePendingAccount(Request $request, User $user){abort_if(strtolower((string)$user->role)==='super_admin',404);$user->update(['account_setup_completed_at'=>now(),'is_active'=>true]);return response()->json(['success'=>true,'data'=>['id'=>$user->id,'status'=>'Approved']]);}

    public function tickets(Request $request)
    {
        $validated = $request->validate(['status' => ['nullable', 'string', 'max:40'], 'search' => ['nullable', 'string', 'max:100']]);
        $query = SupportTicket::query()->latest();
        if ($status = trim((string) ($validated['status'] ?? ''))) $query->where('status', $status);
        if ($search = trim((string) ($validated['search'] ?? ''))) $query->where(fn ($q) => $q->where('ticket_no', 'like', "%{$search}%")->orWhere('subject', 'like', "%{$search}%"));
        $users = User::query()->get()->keyBy('id');
        return response()->json(['success' => true, 'data' => $query->paginate(30)->through(function (SupportTicket $ticket) use ($users) {
            $user = $users->get($ticket->user_id);
            return ['id'=>$ticket->id,'ticket_no' => $ticket->ticket_no, 'date' => optional($ticket->created_at)->toDateString(), 'customer' => $user?->company_name ?: $ticket->requested_by, 'type' => $ticket->ticket_type, 'subject' => $ticket->subject, 'priority' => $ticket->priority, 'assigned_to' => $ticket->responded_by ?: 'Unassigned', 'status' => $ticket->status];
        })]);
    }
    public function ticket(Request $request, SupportTicket $ticket){$activities=SupportTicketActivity::where('support_ticket_id',$ticket->id)->oldest()->get();$user=User::find($ticket->user_id);return response()->json(['success'=>true,'data'=>['ticket'=>['id'=>$ticket->id,'ticket_no'=>$ticket->ticket_no,'customer'=>($user?->company_name) ?: $ticket->requested_by,'customer_name'=>$ticket->requested_by,'email'=>$ticket->email,'phone'=>$ticket->phone,'type'=>$ticket->ticket_type,'subject'=>$ticket->subject,'description'=>$ticket->description,'priority'=>$ticket->priority,'status'=>$ticket->status,'assigned_to'=>$ticket->responded_by ?: 'Unassigned','sent_at'=>$ticket->created_at?->toIso8601String(),'resolved_at'=>$ticket->responded_at?->toIso8601String()],'activities'=>$activities]]);}
    public function ticketAction(Request $request, SupportTicket $ticket){$d=$request->validate(['action'=>'required|in:reply,note,pending,resolve,close','message'=>'nullable|string|max:5000']);$map=['reply'=>['Reply to customer','In Progress'],'note'=>['Internal note',$ticket->status],'pending'=>['Marked pending','Pending'],'resolve'=>['Ticket resolved','Resolved'],'close'=>['Ticket closed','Closed']];[$type,$status]=$map[$d['action']];$actor=$request->user();SupportTicketActivity::create(['support_ticket_id'=>$ticket->id,'actor_id'=>$actor->id,'actor_name'=>$actor->name,'actor_type'=>'super_admin','activity_type'=>$type,'message'=>$d['message']??null,'status'=>$status]);$ticket->update(['status'=>$status,'responded_by'=>$actor->name,'responded_position'=>'Super Admin','responded_at'=>now()]);return $this->ticket($request,$ticket->fresh());}

    public function auditLogs(Request $request)
    {
        $validated = $request->validate(['search' => ['nullable', 'string', 'max:100']]);
        $users = User::query()->get()->keyBy('id');
        $query = AccountLoginHistory::query()->latest('logged_in_at');
        if ($search = trim((string) ($validated['search'] ?? ''))) $query->where('ip_address', 'like', "%{$search}%");
        return response()->json(['success' => true, 'data' => $query->paginate(30)->through(function (AccountLoginHistory $log) use ($users) {
            $user = $users->get($log->user_id);
            return ['date' => optional($log->logged_in_at)->toDateString(), 'time' => optional($log->logged_in_at)->format('g:i A'), 'customer' => $user?->company_name ?: 'Unknown customer', 'user' => $user?->name ?: 'Unknown user', 'module' => 'Login', 'action' => $log->result ?: 'Successful login', 'ip_address' => $log->ip_address, 'device' => trim(($log->device_name ?: '') . ' ' . ($log->browser ?: '')), 'location' => $log->location, 'status' => $log->result ?: 'Successful'];
        })]);
    }
    public function customers(Request $request)
    {
        $validated = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'id' => ['nullable', 'string', 'max:40'], 'phone' => ['nullable', 'string', 'max:40'], 'country' => ['nullable', 'string', 'max:100'], 'state'=>['nullable','string','max:100'], 'city'=>['nullable','string','max:100'], 'from_date'=>['nullable','date'], 'to_date'=>['nullable','date'], 'status' => ['nullable', 'in:all,active,inactive']]);
        $query = User::query()->where('role', '!=', 'super_admin');
        if ($search = trim((string) ($validated['search'] ?? ''))) {
            $query->where(fn ($q) => $q->where('company_name', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }
        if ($id=trim((string)($validated['id']??''))) $query->where('id',$id);
        if ($phone=trim((string)($validated['phone']??''))) $query->where('phone','like',"%{$phone}%");
        if ($country=trim((string)($validated['country']??''))) $query->where('country',$country);
        $state=trim((string)($validated['state']??''));$city=trim((string)($validated['city']??''));if ($state || $city) { $setupIds=AccountSetup::query()->when($state,fn($q)=>$q->where('company_state',$state))->when($city,fn($q)=>$q->where('company_city',$city))->pluck('id');$query->whereIn('project_id',$setupIds); }
        if ($validated['from_date']??null) $query->whereDate('created_at','>=',$validated['from_date']);
        if ($validated['to_date']??null) $query->whereDate('created_at','<=',$validated['to_date']);
        if (($validated['status'] ?? 'all') !== 'all') $query->where('is_active', ($validated['status'] === 'active'));
        $page = $query->latest()->paginate(10);
        $setups = AccountSetup::query()->whereIn('id', $page->getCollection()->pluck('project_id')->filter())->get()->keyBy('id');
        $page->setCollection($page->getCollection()->map(function (User $user) use ($setups) {
            $setup = $setups->get($user->project_id);
            return ['id' => $user->id, 'customer_number' => 'CUST-' . str_pad((string) $user->id, 6, '0', STR_PAD_LEFT), 'company_name' => $user->company_name ?: $user->name, 'contact_name' => $user->name,
                'email' => $user->email, 'phone' => $user->phone, 'country' => $setup?->company_country ?: $user->country, 'state' => $setup?->company_state, 'city' => $setup?->company_city,
                'plan' => ucfirst($user->selected_plan ?: 'Not selected'), 'status' => $user->is_active ? 'Active' : 'Inactive', 'created_at' => optional($user->created_at)->toDateString(), 'years_in_service'=>$user->created_at?->diffForHumans(null,true), 'monthly_payment'=>round(BillingInvoice::where('user_id',$user->id)->where('status','paid')->latest()->value('amount_paid')/100,2)];
        }));
        return response()->json(['success' => true, 'data' => $page]);
    }

    public function customer(Request $request, User $user)
    {
        abort_if(strtolower((string) $user->role) === 'super_admin', 404);
        $setup = $user->project_id ? AccountSetup::find($user->project_id) : null;
        $invoices = BillingInvoice::query()->where('user_id', $user->id)->latest()->limit(6)->get();
        $paid = $invoices->where('status', 'paid');
        return response()->json(['success' => true, 'data' => [
            'customer_number' => 'CUST-' . str_pad((string) $user->id, 6, '0', STR_PAD_LEFT), 'company_name' => $user->company_name ?: $user->name, 'status' => $user->is_active ? 'Active' : 'Inactive',
            'sign_up_date' => optional($user->created_at)->toDateString(), 'plan' => ucfirst($user->selected_plan ?: 'Not selected'), 'billing_cycle' => $setup?->billing_cycle ?: 'Not set',
            'country' => $setup?->company_country ?: $user->country, 'state' => $setup?->company_state, 'city' => $setup?->company_city, 'postal_code' => $setup?->company_zip ?: $user->postal_code,
            'admin' => ['name' => $setup?->admin_first_name ? trim($setup->admin_first_name.' '.$setup->admin_last_name) : $user->name, 'email' => $setup?->admin_email ?: $user->email, 'phone' => $setup?->admin_phone ?: $user->phone],
            'director' => ['name'=>$setup?->director_owner_name ?: $setup?->director_name ?: 'Not provided','email'=>$setup?->director_owner_email ?: $setup?->authorized_contact_email,'phone'=>$setup?->director_owner_phone ?: $setup?->authorized_contact_phone],
            'accounting' => ['name'=>$setup?->accounts_payable_name ?: 'Not provided','email'=>$setup?->accounts_payable_email,'phone'=>$setup?->accounts_payable_phone],
            'company' => ['business_number' => $setup?->business_number, 'tax_number' => $setup?->tax_number, 'address' => $setup?->company_address],
            'users_count' => $user->project_id ? User::query()->where('project_id', $user->project_id)->count() : 1,
            'payment_history' => $invoices->map(fn (BillingInvoice $invoice) => $this->invoiceRow($invoice)),
            'payment_methods'=>collect([['method'=>$setup?->primary_card_type ?: 'Primary payment method','details'=>$setup?->primary_card_last_four ? '•••• '.$setup->primary_card_last_four : 'Not provided','status'=>'Primary'],['method'=>$setup?->backup_card_type ?: 'Backup payment method','details'=>$setup?->backup_card_last_four ? '•••• '.$setup->backup_card_last_four : 'Not provided','status'=>'Backup']])->filter(fn($x)=>$x['details']!=='Not provided')->values(),
            'nsf_records'=>$invoices->whereNotIn('status',['paid'])->map(fn($i)=>['date'=>optional($i->created_at)->toDateString(),'amount'=>round(($i->amount_remaining ?: $i->amount_due)/100,2),'reason'=>ucfirst($i->status ?: 'Unpaid'),'status'=>'Open'])->values(),
            'status_history'=>[['status'=>$user->is_active?'Active':'Inactive','from_date'=>optional($user->created_at)->toDateString(),'to_date'=>null,'reason'=>'Current account status','changed_by'=>'Super Admin']],
            'summary' => ['paid_invoices' => $paid->count(), 'paid_amount' => round($paid->sum('amount_paid') / 100, 2), 'failed_invoices' => $invoices->whereNotIn('status', ['paid'])->count(), 'user_licenses'=>$user->project_id ? User::query()->where('project_id', $user->project_id)->count() : 1],
        ]]);
    }
    public function updateCustomerStatus(Request $request, User $user){abort_if(strtolower((string)$user->role)==='super_admin',404);$d=$request->validate(['status'=>'required|in:active,inactive']);$user->update(['is_active'=>$d['status']==='active']);return response()->json(['success'=>true,'data'=>['status'=>$user->is_active?'Active':'Inactive']]);}
    public function customerUsers(Request $request){$v=$request->validate(['search'=>'nullable|string|max:100','plan'=>'nullable|string|max:100','from_date'=>'nullable|date','to_date'=>'nullable|date']);$query=User::where('role','!=','super_admin');if($v['search']??null)$query->where(fn($q)=>$q->where('company_name','like','%'.$v['search'].'%')->orWhere('name','like','%'.$v['search'].'%'));if($v['plan']??null)$query->where('selected_plan',$v['plan']);$page=$query->latest()->paginate(20);$ids=$page->getCollection()->pluck('id');$logs=AccountLoginHistory::whereIn('user_id',$ids)->when($v['from_date']??null,fn($q)=>$q->whereDate('logged_in_at','>=',$v['from_date']))->when($v['to_date']??null,fn($q)=>$q->whereDate('logged_in_at','<=',$v['to_date']))->get()->groupBy('user_id');$page->setCollection($page->getCollection()->map(function($u)use($logs){$l=$logs->get($u->id,collect());$week=$l->filter(fn($x)=>$x->logged_in_at&&$x->logged_in_at->gte(now()->subWeek()))->count();$month=$l->filter(fn($x)=>$x->logged_in_at&&$x->logged_in_at->gte(now()->subMonth()))->count();return ['id'=>$u->id,'customer_no'=>'CUST-'.str_pad((string)$u->id,6,'0',STR_PAD_LEFT),'company'=>$u->company_name?:$u->name,'plan'=>ucfirst($u->selected_plan?:'Not selected'),'qty_users'=>$u->project_id?User::where('project_id',$u->project_id)->count():1,'usage_date'=>now()->toDateString(),'daily_usage'=>$l->where('logged_in_at','>=',today())->count(),'week_usage'=>$week,'month_usage'=>$month];}));return response()->json(['success'=>true,'data'=>$page]);}
    public function customerUserList(Request $request, User $user){abort_if(strtolower((string)$user->role)==='super_admin',404);$users=User::query()->where('role','!=','super_admin')->when($user->project_id,fn($q)=>$q->where('project_id',$user->project_id),fn($q)=>$q->whereKey($user->id))->orderBy('name')->get();return response()->json(['success'=>true,'data'=>['company'=>$user->company_name?:$user->name,'users'=>$users->map(fn($member)=>['id'=>$member->id,'name'=>$member->name,'email'=>$member->email,'phone'=>$member->phone,'role'=>ucfirst(str_replace('_',' ',$member->role?:'User')),'status'=>$member->is_active?'Active':'Inactive','created_at'=>$member->created_at?->toIso8601String()])]]);}
    public function userBehaviour(Request $request, User $user){abort_if(strtolower((string)$user->role)==='super_admin',404);$v=$request->validate(['from_date'=>'nullable|date','to_date'=>'nullable|date']);$logs=AccountLoginHistory::where('user_id',$user->id)->when($v['from_date']??null,fn($q)=>$q->whereDate('logged_in_at','>=',$v['from_date']))->when($v['to_date']??null,fn($q)=>$q->whereDate('logged_in_at','<=',$v['to_date']))->latest('logged_in_at')->paginate(20);$device=AccountSecurityDevice::where('user_id',$user->id)->latest('last_seen_at')->first();$security=AccountSecurity::where('user_id',$user->id)->first();$logs->setCollection($logs->getCollection()->map(fn($l)=>['login_at'=>$l->logged_in_at?->toIso8601String(),'logout_at'=>null,'duration'=>'—','failed_attempts'=>strtolower($l->result)==='failed'?1:0,'ip'=>$l->ip_address,'location'=>$l->location,'device'=>trim($l->device_name.' / '.$l->browser),'status'=>$l->result,'notes'=>$l->device_name]));$all=AccountLoginHistory::where('user_id',$user->id)->get();return response()->json(['success'=>true,'data'=>['user'=>['id'=>$user->id,'customer_no'=>'CUST-'.str_pad((string)$user->id,6,'0',STR_PAD_LEFT),'company'=>$user->company_name?:$user->name,'name'=>$user->name,'email'=>$user->email,'license_no'=>'LIC-'.str_pad((string)$user->id,6,'0',STR_PAD_LEFT),'allowed_ip'=>$device?->ip_address,'plan'=>ucfirst($user->selected_plan?:'Not selected')],'summary'=>['total_logins'=>$all->count(),'failed_logins'=>$all->filter(fn($l)=>strtolower($l->result)==='failed')->count(),'last_login'=>$all->max('logged_in_at'),'last_logout'=>null],'security'=>['allowed_ip'=>$device?->ip_address,'current_ip'=>$all->first()?->ip_address,'ip_status'=>$device?->ip_address?'Authorized':'Not configured','unauthorized_attempts'=>0,'mfa_enabled'=>$security?->mfa_enabled?'Enabled':'Disabled','password_changed_at'=>$security?->password_changed_at],'device'=>['name'=>$device?->device_name,'type'=>$device?->device_type,'browser'=>$device?->browser,'ip'=>$device?->ip_address,'first_used'=>$device?->registered_at],'logs'=>$logs]]);}

    public function billing(Request $request)
    {
        $validated = $request->validate(['status' => ['nullable', 'in:all,paid,open,uncollectible,void'], 'search' => ['nullable', 'string', 'max:100'], 'from_date'=>['nullable','date'], 'to_date'=>['nullable','date']]);
        $query = BillingInvoice::query()->with('user:id,name,company_name')->latest();
        if (($status = $validated['status'] ?? 'all') !== 'all') $query->where('status', $status);
        if ($search = trim((string) ($validated['search'] ?? ''))) {
            $query->whereHas('user', fn ($q) => $q->where('company_name', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
        }
        if($validated['from_date']??null)$query->whereDate('created_at','>=',$validated['from_date']);if($validated['to_date']??null)$query->whereDate('created_at','<=',$validated['to_date']);
        return response()->json(['success' => true, 'data' => $query->paginate(25)->through(function(BillingInvoice $i){$total=(int)($i->amount_due??((int)$i->amount_paid+(int)$i->amount_remaining));$tax=$i->tax_amount!==null?(int)$i->tax_amount:(int)round($total*5/105);return ['id'=>$i->id,'invoice_number'=>$i->stripe_invoice_id?:'INV-'.$i->id,'customer_no'=>'CUST-'.str_pad((string)$i->user_id,6,'0',STR_PAD_LEFT),'customer'=>$i->user?->company_name?:$i->user?->name?:'Unknown customer','plan'=>$i->plan_name?:'Software Service Plan','service_date'=>optional($i->period_start?:$i->created_at)->toDateString(),'charging_date'=>optional($i->created_at)->toDateString(),'subtotal'=>round(($total-$tax)/100,2),'tax'=>round($tax/100,2),'total'=>round($total/100,2),'paid'=>round((int)$i->amount_paid/100,2),'outstanding'=>round((int)$i->amount_remaining/100,2),'status'=>ucfirst($i->status?:'unknown'),'currency'=>strtoupper($i->currency?:'CAD'),'invoice_pdf'=>$i->invoice_pdf,'invoice_url'=>$i->hosted_invoice_url];})]);
    }

    public function taxReport(Request $request)
    {
        $validated = $request->validate(['from_date' => ['nullable', 'date'], 'to_date' => ['nullable', 'date', 'after_or_equal:from_date'], 'customer_id' => ['nullable', 'integer', 'exists:users,id']]);
        $from = isset($validated['from_date']) ? Carbon::parse($validated['from_date'])->startOfDay() : now()->startOfMonth();
        $to = isset($validated['to_date']) ? Carbon::parse($validated['to_date'])->endOfDay() : now()->endOfMonth();
        $rows = BillingInvoice::query()->with('user:id,name,company_name')->where('status', 'paid')->when($validated['customer_id'] ?? null, fn ($q, $id) => $q->where('user_id', $id))->get()
            ->filter(fn (BillingInvoice $invoice) => ($invoice->period_start ?: $invoice->created_at)?->betweenIncluded($from, $to))->map(function (BillingInvoice $invoice) {
                $paid = (int) $invoice->amount_paid; $tax = $invoice->tax_amount !== null ? (int) $invoice->tax_amount : (int) round($paid * 5 / 105);
                return ['customer' => $invoice->user?->company_name ?: $invoice->user?->name ?: 'Unknown customer', 'date' => optional($invoice->period_start ?: $invoice->created_at)->toDateString(), 'invoice_number' => $invoice->stripe_invoice_id, 'plan' => $invoice->plan_name ?: 'Software Service Plan', 'currency' => strtoupper($invoice->currency ?: 'CAD'), 'subtotal' => round(($paid - $tax) / 100, 2), 'tax' => round($tax / 100, 2), 'total' => round($paid / 100, 2)];
            })->values();
        return response()->json(['success' => true, 'data' => ['from_date' => $from->toDateString(), 'to_date' => $to->toDateString(), 'rows' => $rows, 'totals' => ['subtotal' => round($rows->sum('subtotal'), 2), 'tax' => round($rows->sum('tax'), 2), 'total' => round($rows->sum('total'), 2)]]]);
    }

    public function dashboard(Request $request)
    {
        $customers = User::query()->where('role', '!=', 'super_admin');
        $invoices = BillingInvoice::query();
        $monthStart = now()->startOfMonth();

        $paidThisMonth = (int) (clone $invoices)
            ->where('status', 'paid')
            ->where('created_at', '>=', $monthStart)
            ->sum('amount_paid');
        $today = now()->startOfDay();
        $weekStart = now()->subDays(7)->startOfDay();
        $securityEvents = ExternalAccessAttempt::query();
        $tickets = SupportTicket::query();

        return response()->json(['success' => true, 'data' => [
            'summary' => [
                'total_customers' => (clone $customers)->count(),
                'active_customers' => (clone $customers)->where('is_active', true)->count(),
                'paid_invoices' => (clone $invoices)->where('status', 'paid')->count(),
                'monthly_revenue' => round($paidThisMonth / 100, 2),
                'failed_payments' => (clone $invoices)->whereIn('status', ['open', 'uncollectible', 'void'])->count(),
            ],
            'heads_up' => [
                'sales_today' => round(((int) (clone $invoices)->where('status', 'paid')->where('created_at', '>=', $today)->sum('amount_paid')) / 100, 2),
                'sales_last_week' => round(((int) (clone $invoices)->where('status', 'paid')->where('created_at', '>=', $weekStart)->sum('amount_paid')) / 100, 2),
                'payments_today' => round(((int) (clone $invoices)->where('status', 'paid')->where('created_at', '>=', $today)->sum('amount_paid')) / 100, 2),
                'outstanding_balance' => round(((int) (clone $invoices)->whereIn('status', ['open', 'uncollectible'])->sum('amount_remaining')) / 100, 2),
                'nsf_payments' => (clone $invoices)->where('status', 'uncollectible')->count(),
                'scheduled_due' => round(((int) (clone $invoices)->where('status', 'open')->sum('amount_due')) / 100, 2),
                'gateway_issues' => (clone $invoices)->where('status', 'void')->count(),
                'pending_accounts' => AccountSetup::whereNull('completed_at')->count(),
                'suspended_accounts' => (clone $customers)->where('is_active', false)->count(),
                'cancel_requests' => 0,
                'users_online' => AccountLoginHistory::where('logged_in_at', '>=', $today)->distinct('user_id')->count('user_id'),
                'failed_logins' => (clone $securityEvents)->where('result', 'Failed')->count(),
                'locked_users' => 0,
                'password_resets' => 0,
                'unknown_devices' => (clone $securityEvents)->whereIn('threat_level', ['high', 'critical'])->count(),
                'licence_sharing' => 0,
                'critical_alerts' => (clone $securityEvents)->where('threat_level', 'critical')->count(),
                'internal_alerts' => 0,
                'external_alerts' => (clone $securityEvents)->whereIn('threat_level', ['medium', 'high', 'critical'])->count(),
                'system_issues' => 0,
                'server_issues' => 0,
                'backup_status' => 'Healthy',
                'urgent_messages' => 0,
                'high_priority_tickets' => (clone $tickets)->where('priority', 'High')->whereNotIn('status', ['Resolved', 'Closed'])->count(),
                'unanswered_tickets' => (clone $tickets)->whereIn('status', ['Awaiting Response', 'Pending'])->count(),
                'customer_complaints' => (clone $tickets)->where('ticket_type', 'Report an Issue')->count(),
            ],
            'plan_distribution' => (clone $customers)
                ->selectRaw("COALESCE(NULLIF(selected_plan, ''), 'not_selected') as plan, COUNT(*) as total")
                ->groupBy('plan')->orderBy('plan')->get(),
            'recent_customers' => (clone $customers)->latest()->limit(6)->get([
                'id', 'name', 'company_name', 'email', 'selected_plan', 'is_active', 'created_at',
            ])->map(fn (User $user) => [
                'id' => $user->id,
                'company_name' => $user->company_name ?: $user->name,
                'email' => $user->email,
                'plan' => ucfirst($user->selected_plan ?: 'Not selected'),
                'status' => $user->is_active ? 'Active' : 'Inactive',
                'created_at' => optional($user->created_at)->toDateString(),
            ]),
            'recent_payments' => (clone $invoices)->where('status', 'paid')->latest()->limit(6)->get()
                ->map(function (BillingInvoice $invoice) {
                    $customer = User::find($invoice->user_id);
                    return [
                        'invoice_number' => $invoice->stripe_invoice_id,
                        'customer' => $customer?->company_name ?: $customer?->name ?: 'Unknown customer',
                        'amount' => round(((int) $invoice->amount_paid) / 100, 2),
                        'currency' => strtoupper($invoice->currency ?: 'CAD'),
                        'date' => optional($invoice->created_at)->toDateString(),
                    ];
                }),
        ]]);
    }

    /** Data source for the detailed Super Admin report pages. */
    public function advancedReport(Request $request, string $report)
    {
        abort_unless(in_array($report, ['plan-history','audit-log','failed-logins','suspicious-devices','suspicious-activities','payment-failures','customer-revenue','emergency-shutdown','system-usage','customer-master','sales-master','sales-master-all','sales-master-customer','sales-tax-master','sales-tax-all','sales-tax-customer','suspected-license-sharing','sign-up-created-accounts'], true), 404);
        $users = User::where('role', '!=', 'super_admin')->get()->keyBy('id');
        $money = fn ($cents) => round(((int) $cents) / 100, 2);
        $invoices = BillingInvoice::with('user:id,name,company_name,selected_plan,is_active,country')->latest()->get();
        $invoiceRows = $invoices->map(function (BillingInvoice $i) use ($money) {
            $total = (int) ($i->amount_due ?? ((int) $i->amount_paid + (int) $i->amount_remaining));
            $tax = $i->tax_amount !== null ? (int) $i->tax_amount : (int) round($total * 5 / 105);
            return ['date' => optional($i->period_start ?: $i->created_at)->toDateString(), 'invoice_no' => $i->stripe_invoice_id ?: ('INV-' . $i->id), 'customer_no' => 'CA-' . str_pad((string) $i->user_id, 5, '0', STR_PAD_LEFT), 'customer' => $i->user?->company_name ?: $i->user?->name ?: 'Unknown customer', 'country' => $i->user?->country ?: '—', 'plan' => $i->plan_name ?: ucfirst($i->user?->selected_plan ?: 'Software Service Plan'), 'currency' => strtoupper($i->currency ?: 'CAD'), 'subtotal' => $money($total - $tax), 'tax' => $money($tax), 'total' => $money($total), 'paid' => $money($i->amount_paid), 'outstanding' => $money($i->amount_remaining), 'status' => ucfirst($i->status ?: 'unknown')];
        })->values();
        if (in_array($report, ['sales-master', 'sales-master-all'], true)) return response()->json(['success' => true, 'data' => ['rows' => $invoiceRows, 'summary' => ['Total Invoices' => $invoiceRows->count(), 'Total Sales' => $invoiceRows->sum('subtotal'), 'Sales Tax' => $invoiceRows->sum('tax'), 'Payments Received' => $invoiceRows->sum('paid'), 'Outstanding Balance' => $invoiceRows->sum('outstanding')]]]);
        if ($report === 'sales-master-customer') { $rows=$invoiceRows->groupBy('customer_no')->map(function($group){$r=$group->first();return ['customer_no'=>$r['customer_no'],'customer'=>$r['customer'],'country'=>$r['country'],'plan'=>$r['plan'],'total_invoices'=>$group->count(),'subtotal'=>$group->sum('subtotal'),'sales_tax'=>$group->sum('tax'),'total_sales'=>$group->sum('total'),'payments_received'=>$group->sum('paid'),'outstanding'=>$group->sum('outstanding'),'last_invoice_date'=>$group->max('date'),'payment_status'=>$group->every(fn($x)=>$x['status']==='Paid')?'Paid':'Partial'];})->values();return response()->json(['success'=>true,'data'=>['rows'=>$rows,'summary'=>['Total Customers'=>$rows->count(),'Total Invoices'=>$invoiceRows->count(),'Total Sales'=>$rows->sum('total_sales'),'Sales Tax'=>$rows->sum('sales_tax'),'Payments Received'=>$rows->sum('payments_received'),'Outstanding Balance'=>$rows->sum('outstanding')]]]); }
        if (in_array($report,['sales-tax-master','sales-tax-all'],true)) { $rows=$invoiceRows->map(fn($r)=>['invoice_date'=>$r['date'],'invoice_no'=>$r['invoice_no'],'customer_no'=>$r['customer_no'],'customer'=>$r['customer'],'country'=>$r['country'],'tax_jurisdiction'=>$r['country']==='Canada'?'Canada GST':'Sales tax','taxable_sales'=>$r['subtotal'],'tax_exempt_sales'=>0,'tax_rate'=>$r['subtotal']>0?round(($r['tax']/$r['subtotal'])*100,2):0,'sales_tax'=>$r['tax'],'total_amount'=>$r['total'],'payment_status'=>$r['status']]);return response()->json(['success'=>true,'data'=>['rows'=>$rows,'summary'=>['Total Taxable Sales'=>$rows->sum('taxable_sales'),'Total Sales Tax Collected'=>$rows->sum('sales_tax'),'Tax-Exempt Sales'=>0,'Tax Remitted'=>0,'Outstanding Tax Balance'=>$rows->where('payment_status','!=','Paid')->sum('sales_tax'),'Total Tax Records'=>$rows->count()]]]); }
        if ($report === 'sales-tax-customer') { $rows=$invoiceRows->groupBy('customer_no')->map(function($group){$r=$group->first();return ['customer_no'=>$r['customer_no'],'customer'=>$r['customer'],'country'=>$r['country'],'tax_jurisdiction'=>$r['country']==='Canada'?'Canada GST':'Sales tax','total_invoices'=>$group->count(),'taxable_sales'=>$group->sum('subtotal'),'tax_exempt_sales'=>0,'sales_tax'=>$group->sum('tax'),'total_sales'=>$group->sum('total'),'tax_remitted'=>0,'outstanding_tax'=>$group->where('status','!=','Paid')->sum('tax'),'last_invoice_date'=>$group->max('date'),'filing_status'=>$group->every(fn($x)=>$x['status']==='Paid')?'Filed':'Pending','payment_status'=>$group->every(fn($x)=>$x['status']==='Paid')?'Paid':'Partial'];})->values();return response()->json(['success'=>true,'data'=>['rows'=>$rows,'summary'=>['Total Customers'=>$rows->count(),'Total Taxable Sales'=>$rows->sum('taxable_sales'),'Total Sales Tax Collected'=>$rows->sum('sales_tax'),'Tax-Exempt Sales'=>0,'Tax Remitted'=>0,'Outstanding Tax Balance'=>$rows->sum('outstanding_tax')]]]); }
        if ($report === 'customer-revenue') { $rows = $invoiceRows->groupBy('customer_no')->map(function ($group) { $first = $group->first(); return ['customer_no'=>$first['customer_no'],'customer'=>$first['customer'],'plan'=>$first['plan'],'country'=>$first['country'],'currency'=>$first['currency'],'recurring_revenue'=>$group->sum('subtotal'),'tax'=>$group->sum('tax'),'total_revenue'=>$group->sum('total'),'payments_received'=>$group->sum('paid'),'outstanding'=>$group->sum('outstanding'),'status'=>$group->every(fn($r)=>$r['status']==='Paid')?'Active':'Attention']; })->values(); return response()->json(['success'=>true,'data'=>['rows'=>$rows,'summary'=>['Total Revenue'=>$rows->sum('total_revenue'),'Customers'=>$rows->count(),'Payments Received'=>$rows->sum('payments_received'),'Outstanding'=>$rows->sum('outstanding')]]]); }
        if ($report === 'customer-master') { $rows = $users->map(function ($u) use ($invoiceRows) { $group=$invoiceRows->where('customer_no','CA-'.str_pad((string)$u->id,5,'0',STR_PAD_LEFT)); return ['customer_no'=>'CA-'.str_pad((string)$u->id,5,'0',STR_PAD_LEFT),'customer'=>$u->company_name?:$u->name,'country'=>$u->country?:'—','plan'=>ucfirst($u->selected_plan?:'Not selected'),'monthly_payment'=>$group->sum('total'),'total_paid'=>$group->sum('paid'),'status'=>$u->is_active?'Active':'Inactive','created_at'=>$u->created_at?->toDateString()]; })->values(); return response()->json(['success'=>true,'data'=>['rows'=>$rows,'summary'=>['Total Customers'=>$rows->count(),'Active Customers'=>$rows->where('status','Active')->count(),'Monthly Billing'=>$rows->sum('monthly_payment')]]]); }
        if ($report === 'plan-history') { $logs=PlanChangeLog::latest()->get(); $rows=$logs->map(function($l)use($users){$u=$users->get($l->user_id);return ['date'=>$l->created_at?->toDateString(),'customer_no'=>'CA-'.str_pad((string)$l->user_id,5,'0',STR_PAD_LEFT),'customer'=>$u?->company_name?:$u?->name?:'Unknown','previous_plan'=>ucfirst($l->from_plan?:'Not selected'),'new_plan'=>ucfirst($l->to_plan?:'Not selected'),'change_type'=>strtolower((string)$l->to_plan)>strtolower((string)$l->from_plan)?'Upgrade':'Plan update','changed_by'=>$users->get($l->changed_by_user_id)?->name?:'System'];});return response()->json(['success'=>true,'data'=>['rows'=>$rows,'summary'=>['Total Plan Changes'=>$rows->count(),'Upgrades'=>$rows->where('change_type','Upgrade')->count(),'Plan Updates'=>$rows->where('change_type','Plan update')->count()]]]); }
        if ($report === 'suspected-license-sharing') { $logs=AccountLoginHistory::latest('logged_in_at')->limit(1000)->get()->groupBy('user_id');$rows=$logs->filter(fn($group)=>$group->pluck('ip_address')->filter()->unique()->count()>1 || $group->pluck('device_id')->filter()->unique()->count()>1)->map(function($group,$userId)use($users){$u=$users->get($userId);$ips=$group->pluck('ip_address')->filter()->unique();$devices=$group->pluck('device_id')->filter()->unique();$risk=$ips->count()>=4||$devices->count()>=4?'High':($ips->count()>=2||$devices->count()>=2?'Medium':'Low');return ['customer_no'=>'CA-'.str_pad((string)$userId,5,'0',STR_PAD_LEFT),'customer'=>$u?->company_name?:'Unknown customer','user'=>$u?->name?:'Unknown user','email'=>$u?->email?:'—','plan'=>ucfirst($u?->selected_plan?:'Not selected'),'detection_rule'=>$ips->count()>1?'Multiple IPs / locations':'Multiple devices','risk'=>$risk,'first_detected'=>$group->min('logged_in_at')?->toDateTimeString(),'last_detected'=>$group->max('logged_in_at')?->toDateTimeString(),'devices_locations'=>$devices->count().' devices / '.$ips->count().' IPs','device_limit'=>2,'status'=>'Active'];})->values();return response()->json(['success'=>true,'data'=>['rows'=>$rows,'summary'=>['Total Customers'=>$users->count(),'Total Users'=>$users->count(),'Suspected Users'=>$rows->count(),'High Risk'=>$rows->where('risk','High')->count(),'Medium Risk'=>$rows->where('risk','Medium')->count(),'Low Risk'=>$rows->where('risk','Low')->count(),'Active'=>$rows->where('status','Active')->count()]]]); }
        if ($report === 'sign-up-created-accounts') { $rows=$users->sortByDesc('created_at')->map(function($u){$setup=AccountSetup::where('project_id',$u->id)->first();$status=$u->account_setup_completed_at?'Approved':'Under Review';return ['sign_up_date'=>$u->created_at?->toDateTimeString(),'customer_no'=>'CA-'.str_pad((string)$u->id,5,'0',STR_PAD_LEFT),'customer'=>$u->company_name?:$u->name,'country'=>$u->country?:'—','email'=>$u->email,'phone'=>$u->phone?:'—','plan'=>ucfirst($u->selected_plan?:'Not selected'),'source'=>'Website sign up','created_on'=>$u->created_at?->toDateTimeString(),'account_status'=>$status,'payment_method'=>$setup?->payment_method?:'—'];})->values();return response()->json(['success'=>true,'data'=>['rows'=>$rows,'summary'=>['Total Sign Ups'=>$rows->count(),'Accounts Created'=>$rows->where('account_status','Approved')->count(),'Under Review'=>$rows->where('account_status','Under Review')->count(),'Approved'=>$rows->where('account_status','Approved')->count(),'Conversion Rate'=>$rows->count()?round($rows->where('account_status','Approved')->count()*100/$rows->count(),2):0]]]); }
        if ($report === 'emergency-shutdown') { $rows=EmergencyShutdown::latest('starts_at')->get()->map(function($s)use($users){return ['date'=>$s->starts_at?->toDateString(),'shutdown_id'=>'ES-'.$s->id,'scope'=>ucwords(str_replace('_',' ',$s->scope)),'customer'=>$s->customer_id?($users->get($s->customer_id)?->company_name?:'Customer account'):'Platform / selected customers','reason'=>$s->reason,'risk'=>ucfirst($s->risk_level),'expected_restore'=>$s->expected_restore_at?->toDateTimeString(),'actual_restore'=>$s->restored_at?->toDateTimeString(),'status'=>ucfirst($s->status),'requested_by'=>$users->get($s->requested_by)?->name?:'System'];});return response()->json(['success'=>true,'data'=>['rows'=>$rows,'summary'=>['Total Shutdown Events'=>$rows->count(),'Active Shutdowns'=>$rows->where('status','Active')->count(),'Restored Systems'=>$rows->where('status','Completed')->count()]]]); }
        if ($report === 'system-usage') { $logs=AccountLoginHistory::latest('logged_in_at')->limit(1000)->get();$rows=$logs->map(function($l)use($users){$u=$users->get($l->user_id);return ['date'=>$l->logged_in_at?->toDateString(),'user'=>$u?->name?:'Unknown user','customer'=>$u?->company_name?:'—','device'=>$l->device_name?:'Unknown','browser'=>$l->browser?:'Unknown','ip'=>$l->ip_address?:'—','location'=>$l->location?:'—','result'=>$l->result?:'Successful'];});return response()->json(['success'=>true,'data'=>['rows'=>$rows,'summary'=>['Total Users'=>$users->count(),'Total Logins'=>$rows->count(),'Active Users'=>$rows->pluck('user')->unique()->count(),'Transactions'=>$invoices->count()]]]); }
        if (in_array($report,['failed-logins','suspicious-devices','suspicious-activities'],true)) { $items=ExternalAccessAttempt::latest()->get(); if($report==='failed-logins')$items=$items->where('result','Failed'); if($report!=='failed-logins')$items=$items->filter(fn($x)=>in_array(strtolower((string)$x->threat_level),['medium','high','critical']) || $x->result==='Blocked'); $rows=$items->values()->map(fn($x)=>['date'=>$x->created_at?->toDateTimeString(),'ip'=>$x->ip_address?:'—','device'=>$x->user_agent?:'Unknown device','activity'=>$x->target_area?:'Login access','risk'=>ucfirst($x->threat_level?:'low'),'result'=>$x->result?:'Flagged','vpn_proxy'=>$x->vpn_proxy?'Yes':'No']);return response()->json(['success'=>true,'data'=>['rows'=>$rows,'summary'=>['Total Events'=>$rows->count(),'High Risk'=>$rows->where('risk','High')->count()+$rows->where('risk','Critical')->count(),'Unique IP Addresses'=>$rows->pluck('ip')->unique()->count(),'Blocked'=>$rows->where('result','Blocked')->count()]]]); }
        if ($report === 'payment-failures') { $rows=$invoiceRows->filter(fn($r)=>$r['status']!=='Paid')->values()->map(fn($r)=>$r+['failure_type'=>$r['status']==='Void'?'Void / cancelled':'Unpaid or partial','reason'=>'Payment not completed']);return response()->json(['success'=>true,'data'=>['rows'=>$rows,'summary'=>['Total Failures'=>$rows->count(),'Total Amount'=>$rows->sum('total'),'Outstanding'=>$rows->sum('outstanding'),'Affected Customers'=>$rows->pluck('customer_no')->unique()->count()]]]); }
        $logs=AccountLoginHistory::latest('logged_in_at')->limit(1000)->get(); $rows=$logs->map(function($l)use($users){$u=$users->get($l->user_id);return ['date'=>$l->logged_in_at?->toDateString(),'time'=>$l->logged_in_at?->format('h:i A'),'log_id'=>'AL-'.$l->id,'actor'=>$u?->name?:'System','actor_type'=>$u?->role?:'System','customer'=>$u?->company_name?:'Internal system','action'=>'Login','module'=>'Authentication','ip'=>$l->ip_address?:'—','result'=>$l->result?:'Successful'];});return response()->json(['success'=>true,'data'=>['rows'=>$rows,'summary'=>['Total Audit Logs'=>$rows->count(),'User Activities'=>$rows->where('actor_type','!=','super_admin')->count(),'Login Events'=>$rows->count(),'Security Events'=>ExternalAccessAttempt::count()]]]);
    }

    private function invoiceRow(BillingInvoice $invoice): array
    {
        return ['invoice_number' => $invoice->stripe_invoice_id, 'customer' => $invoice->user?->company_name ?: $invoice->user?->name ?: 'Unknown customer', 'plan' => $invoice->plan_name ?: 'Software Service Plan', 'status' => ucfirst($invoice->status ?: 'unknown'), 'amount_paid' => round(((int) $invoice->amount_paid) / 100, 2), 'currency' => strtoupper($invoice->currency ?: 'CAD'), 'date' => optional($invoice->period_start ?: $invoice->created_at)->toDateString()];
    }
}
