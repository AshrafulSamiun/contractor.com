<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WorkforceWorkSchedule extends Model { protected $fillable = ['user_id','project_id','record_no','record_date','title','request_type','staff_name','assigned_to','location_site','customer_job_site','department','start_at','end_at','status','priority','notes','details_json']; protected $casts = ['record_date'=>'date','start_at'=>'datetime','end_at'=>'datetime','details_json'=>'array']; }
