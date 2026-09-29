<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_sites', function (Blueprint $table) {
            $table->id();
            $table->string('job_site_no', 50)->unique();
            $table->string('job_site_name', 150);
            $table->string('contact_no', 20)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->string('customer_no', 50);
            $table->string('customer_name', 150);
            $table->text('address')->nullable();
            $table->string('contact_person', 150);
            $table->string('phone', 20);
            $table->string('email', 100)->nullable();
            $table->tinyInteger('status')->default(1)->comment('1 = active, 2 = inactive');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('project_id')->default(1);
            $table->unsignedBigInteger('inserted_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->index('job_site_no');
            $table->index('customer_no');
            $table->index('status');
            $table->index('is_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_sites');
    }
};
