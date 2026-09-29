<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SupportTicketActivity extends Model {protected $fillable=['support_ticket_id','actor_id','actor_name','actor_type','activity_type','message','status'];}
