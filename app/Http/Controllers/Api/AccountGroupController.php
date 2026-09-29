<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountGroupController extends Controller
{
    public function index(Request $request) { $q=AccountGroup::where('project_id',$request->user()->project_id)->where('is_deleted',0); if($request->filled('search')){$s=$request->string('search')->trim();$q->where(fn($x)=>$x->where('sub_group','like',"%{$s}%")->orWhere('sub_group_code','like',"%{$s}%"));} return response()->json(['success'=>true,'data'=>$q->latest()->get()]); }
    public function options() { return response()->json(['success'=>true,'data'=>['main_groups'=>['1'=>'Assets','2'=>'Liabilities','3'=>'Equity','4'=>'Revenue','5'=>'Expenses'],'statement_types'=>['1'=>'Balance Sheet','2'=>'Income Statement','3'=>'Cash Flow'],'account_types'=>['1'=>'Asset','2'=>'Liability','3'=>'Equity','4'=>'Income','5'=>'Expense'],'cash_flow_groups'=>['1'=>'Operating','2'=>'Investing','3'=>'Financing']]]); }
    public function store(Request $request) { $p=$request->user()->project_id; $group=DB::transaction(fn()=>AccountGroup::create($this->validated($request)+['project_id'=>$p,'sub_group_code'=>$this->nextCode($p),'inserted_by'=>$request->user()->id])); return response()->json(['success'=>true,'data'=>$group],201); }
    public function show(Request $request, AccountGroup $accountGroup) { $this->guard($request,$accountGroup); return response()->json(['success'=>true,'data'=>$accountGroup]); }
    public function update(Request $request, AccountGroup $accountGroup) { $this->guard($request,$accountGroup); $accountGroup->update($this->validated($request)+['updated_by'=>$request->user()->id]); return response()->json(['success'=>true,'data'=>$accountGroup->fresh()]); }
    public function destroy(Request $request, AccountGroup $accountGroup) { $this->guard($request,$accountGroup); $accountGroup->update(['is_deleted'=>1,'status_active'=>false,'updated_by'=>$request->user()->id]); return response()->json(['success'=>true]); }
    private function guard(Request $request, AccountGroup $group): void { abort_unless((int)$group->project_id===(int)$request->user()->project_id,404); }
    private function validated(Request $r): array { return $r->validate(['main_group'=>['required','integer'],'sub_group'=>['required','string','max:100'],'statement_type'=>['required','integer'],'account_type'=>['required','integer'],'cash_flow_group'=>['nullable','integer'],'retained_earnings'=>['nullable','boolean'],'status_active'=>['required','boolean']]); }
    private function nextCode($p): string { $last=AccountGroup::where('project_id',$p)->where('sub_group_code','like','AG-%')->lockForUpdate()->orderByDesc('id')->value('sub_group_code'); return 'AG-'.str_pad((string)($last?(int)substr($last,3)+1:1),3,'0',STR_PAD_LEFT); }
}
