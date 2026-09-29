<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_setups', function (Blueprint $table) {
            $table->boolean('declaration_ack')->default(false);
            $table->string('declaration_full_name', 150)->nullable();
            $table->string('declaration_position_title', 150)->nullable();
            $table->date('declaration_signed_at')->nullable();
            $table->string('declaration_initials', 10)->nullable();
            $table->string('declaration_signature', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('account_setups', function (Blueprint $table) {
            $table->dropColumn(['declaration_ack', 'declaration_full_name', 'declaration_position_title', 'declaration_signed_at', 'declaration_initials', 'declaration_signature']);
        });
    }
};
