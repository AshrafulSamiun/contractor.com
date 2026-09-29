<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AccountNotificationSetting extends Model { protected $fillable=['project_id','user_id','email_enabled','phone_enabled','in_app_enabled','recipients','preferences','inserted_by','updated_by']; protected $casts=['project_id'=>'integer','user_id'=>'integer','email_enabled'=>'boolean','phone_enabled'=>'boolean','in_app_enabled'=>'boolean','recipients'=>'array','preferences'=>'array']; }
