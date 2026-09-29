<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;class WebsiteVisit extends Model{protected $fillable=['visitor_key','path','referrer','ip_address','user_agent'];}
