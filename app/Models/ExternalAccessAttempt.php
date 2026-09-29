<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;class ExternalAccessAttempt extends Model{protected $fillable=['ip_address','user_agent','target_area','result','threat_level','vpn_proxy'];protected $casts=['vpn_proxy'=>'boolean'];}
