<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;class EmergencyShutdown extends Model{protected $fillable=['scope','customer_id','reason','risk_level','starts_at','expected_restore_at','message','status','requested_by','approved_by','restored_at','restored_by'];protected $casts=['starts_at'=>'datetime','expected_restore_at'=>'datetime','restored_at'=>'datetime'];}
