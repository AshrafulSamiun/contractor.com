<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estimations', function (Blueprint $table) {
            $table->id();
            $table->string('estimation_no', 50)->unique();
            $table->date('issue_date');
            $table->dateTime('expire_date');
            $table->tinyInteger('currency')->default(1)->comment('1=USD, 2=EUR, 3=GBP, 4=BDT');
            $table->tinyInteger('status')->default(1)->comment('1=Pending, 2=Approved, 3=Cancelled, 4=Expired');
            $table->tinyInteger('job_type')->comment('1=Plumbing, 2=Painting, 3=Electrical, 4=Cleaning, 5=Carpentry, 6=Others');
            $table->text('job_description')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('job_site_id')->nullable();
            $table->timestamp('schedule_start_date')->nullable();
            $table->timestamp('schedule_end_date')->nullable();
            $table->text('scope_of_work')->nullable();
            $table->decimal('sub_total', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->nullable();
            $table->decimal('discount', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->text('note')->nullable();
            $table->tinyInteger('payment_method')->nullable()->comment('1=Cash, 2=Credit Card, 3=Debit Card, 4=Bank Transfer, 5=Others');
            $table->boolean('deposit_required')->default(false);
            $table->text('payment_term')->nullable();
            $table->timestamp('customer_approval_date')->nullable();
            $table->string('customer_approve_by', 300)->nullable();
            $table->tinyInteger('approval_method')->nullable()->comment('1=Phone Call, 2=Text Message, 3=Email, 4=In Person, 5=Mail');
            $table->boolean('convert_to_job_order')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('account_holders')->onDelete('set null');
            $table->foreign('job_site_id')->references('id')->on('job_sites')->onDelete('set null');
            $table->index('estimation_no');
            $table->index('status');
            $table->index('customer_id');
            $table->index('job_site_id');
        });

        Schema::create('estimation_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('estimation_id');
            $table->string('item_name', 150);
            $table->string('item_description', 500)->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('sale_tax_percentage', 5, 2)->nullable();
            $table->decimal('sale_tax_amount', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('estimation_id')->references('id')->on('estimations')->onDelete('cascade');
            $table->index('estimation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estimation_details');
        Schema::dropIfExists('estimations');
    }
};