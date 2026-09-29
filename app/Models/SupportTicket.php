<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SupportTicket extends Model {protected $fillable=['project_id','user_id','ticket_no','ticket_type','requested_by','position','email','phone','subject','description','priority','status','confidential','responded_by','responded_position','responded_at'];protected $casts=['project_id'=>'integer','user_id'=>'integer','confidential'=>'boolean','responded_at'=>'datetime'];}
