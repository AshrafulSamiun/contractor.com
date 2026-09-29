<?php
namespace Database\Seeders;
use App\Models\AccountGroup;
use Illuminate\Database\Seeder;
class AccountGroupSeeder extends Seeder { public function run(): void { $p=(int)env('SEED_PROJECT_ID',16); foreach ([['AG-001',1,'Current Assets',1,1,1],['AG-002',1,'Fixed Assets',1,1,2],['AG-003',2,'Current Liabilities',1,2,1],['AG-004',3,'Owner Equity',1,3,3],['AG-005',4,'Sales Revenue',2,4,1],['AG-006',5,'Operating Expenses',2,5,1]] as [$code,$main,$name,$statement,$type,$cash]) AccountGroup::updateOrCreate(['project_id'=>$p,'sub_group_code'=>$code],['project_id'=>$p,'sub_group_code'=>$code,'main_group'=>$main,'sub_group'=>$name,'statement_type'=>$statement,'account_type'=>$type,'cash_flow_group'=>$cash,'retained_earnings'=>false,'status_active'=>true,'is_deleted'=>false,'inserted_by'=>0,'updated_by'=>0]); } }
