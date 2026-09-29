<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->string('bank_no', 60);
            $table->string('bank_name');
            $table->boolean('status_active')->default(true)->index();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->unsignedBigInteger('linked_gl_account_id')->nullable()->index();
            $table->string('account_name');
            $table->string('account_number', 120);
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->date('opening_balance_date')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['project_id', 'bank_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_profiles');
    }
};
