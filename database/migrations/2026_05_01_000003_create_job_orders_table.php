<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_id')->nullable();
            $table->unsignedBigInteger('estimation_id')->nullable();
            $table->string('job_order_no', 50)->unique();
            $table->date('issue_date');
            $table->tinyInteger('status')->default(1)->comment('1=Pending,2=Scheduled,3=In Progress,4=On Hold,5=Completed,6=Cancelled');
            $table->tinyInteger('job_type')->nullable()->comment('1=Plumbing,2=Painting,3=Electrical,4=Cleaning,5=Carpentry,6=Others');
            $table->text('job_description')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('job_site_id')->nullable();
            $table->string('site_contact_person', 255)->nullable();
            $table->string('site_contact_number', 255)->nullable();
            $table->string('map_link', 500)->nullable();
            $table->timestamp('schedule_start_date')->nullable();
            $table->timestamp('schedule_end_date')->nullable();
            $table->text('scope_of_work')->nullable();
            $table->decimal('sub_total', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('note')->nullable();
            $table->tinyInteger('payment_method')->nullable()->comment('1=Cash,2=Credit Card,3=Debit Card,4=Bank Transfer,5=Other');
            $table->decimal('deposit_received', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('outstanding_balance', 15, 2)->default(0);
            $table->tinyInteger('payment_status')->default(1)->comment('1=Not Paid,2=Partially Paid,3=Fully Paid');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->boolean('converted_to_invoice')->default(false);
            $table->string('invoice_reference', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('quotation_id')->references('id')->on('quotations')->nullOnDelete();
            $table->foreign('estimation_id')->references('id')->on('estimations')->nullOnDelete();
            $table->foreign('customer_id')->references('id')->on('account_holders')->nullOnDelete();
            $table->foreign('job_site_id')->references('id')->on('job_sites')->nullOnDelete();
            $table->index(['status', 'customer_id']);
        });

        Schema::create('job_order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_order_id');
            $table->string('item_name', 150);
            $table->string('item_description', 500)->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('sale_tax_percentage', 5, 2)->default(0);
            $table->decimal('sale_tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('job_order_id')->references('id')->on('job_orders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_order_details');
        Schema::dropIfExists('job_orders');
    }
};
