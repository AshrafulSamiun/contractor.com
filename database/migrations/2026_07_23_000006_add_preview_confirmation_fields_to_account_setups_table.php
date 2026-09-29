<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_setups', function (Blueprint $table) {
            $table->boolean('preview_confirm_ack')->default(false);
            $table->string('preview_full_name', 150)->nullable();
            $table->string('preview_position_title', 150)->nullable();
            $table->string('preview_initials', 10)->nullable();
            $table->string('preview_signature', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('account_setups', function (Blueprint $table) {
            $table->dropColumn(['preview_confirm_ack', 'preview_full_name', 'preview_position_title', 'preview_initials', 'preview_signature']);
        });
    }
};
