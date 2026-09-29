<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AccountMessage extends Model {
    protected $fillable=['project_id','user_id','message_no','direction','folder','status','from_name','to_name','subject','message','attachments','sent_at','read_at','deleted_at','deleted_from'];
    protected $casts=['project_id'=>'integer','user_id'=>'integer','attachments'=>'array','sent_at'=>'datetime','read_at'=>'datetime','deleted_at'=>'datetime'];
}
