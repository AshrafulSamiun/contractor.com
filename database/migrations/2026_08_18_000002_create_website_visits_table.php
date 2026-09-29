<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration {public function up():void{Schema::create('website_visits',function(Blueprint $t){$t->id();$t->string('visitor_key',64)->index();$t->string('path',255)->nullable();$t->string('referrer',500)->nullable();$t->string('ip_address',64)->nullable();$t->string('user_agent',1000)->nullable();$t->timestamps();$t->index('created_at');});}public function down():void{Schema::dropIfExists('website_visits');}};
