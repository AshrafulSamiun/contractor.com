<?php

namespace Database\Seeders;

use App\Models\Estimation;
use App\Models\JobOrder;
use App\Models\JobOrderDetail;
use App\Models\JobSite;
use App\Models\Quotation;
use App\Models\AccountHolder;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class JobOrderSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        JobOrderDetail::truncate();
        JobOrder::truncate();
        Schema::enableForeignKeyConstraints();

        $customerIds = AccountHolder::query()->pluck('id')->all();
        $jobSiteIds = JobSite::query()->pluck('id')->all();
        $quotationIds = Quotation::query()->pluck('id')->all();
        $estimationIds = Estimation::query()->pluck('id')->all();

        $statuses = [1, 2, 3, 4, 5, 6];
        $jobTypes = [1, 2, 3, 4, 5, 6];
        $paymentMethods = [1, 2, 3, 4, 5];
        $paymentStatuses = [1, 2, 3];
        $descriptions = [
            'Downtown Plaza Renovation',
            'Riverside Complex Electrical Upgrade',
            'Oakwood Residence Plumbing Installation',
            'Skyline Tower Interior Painting',
            'Metro Mall Maintenance Contract',
            'Greenfield Office HVAC Support',
            'Sunrise Villa Cleaning Service',
            'Harbor Point Carpentry Works',
            'Central Hub Equipment Setup',
            'North End Facility Repair',
        ];
        $items = ['Material', 'Labor', 'Overhead', 'Transport', 'Equipment'];

        for ($i = 1; $i <= 10; $i++) {
            $issueDate = Carbon::now()->subDays(rand(5, 90));
            $startDate = (clone $issueDate)->addDays(rand(1, 10));
            $endDate = (clone $startDate)->addDays(rand(2, 20));

            $jobOrder = JobOrder::create([
                'quotation_id' => $quotationIds ? $quotationIds[array_rand($quotationIds)] : null,
                'estimation_id' => $estimationIds ? $estimationIds[array_rand($estimationIds)] : null,
                'job_order_no' => 'JOB-' . date('Y') . '-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'issue_date' => $issueDate->format('Y-m-d'),
                'status' => $statuses[array_rand($statuses)],
                'job_type' => $jobTypes[array_rand($jobTypes)],
                'job_description' => $descriptions[$i - 1],
                'customer_id' => $customerIds ? $customerIds[array_rand($customerIds)] : null,
                'job_site_id' => $jobSiteIds ? $jobSiteIds[array_rand($jobSiteIds)] : null,
                'site_contact_person' => 'Site Supervisor ' . $i,
                'site_contact_number' => '01700000' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'map_link' => 'https://maps.google.com/?q=job+site+' . $i,
                'schedule_start_date' => $startDate->format('Y-m-d H:i:s'),
                'schedule_end_date' => $endDate->format('Y-m-d H:i:s'),
                'scope_of_work' => 'Complete assigned work package with labor, materials, and site supervision.',
                'sub_total' => 0,
                'tax' => 0,
                'discount' => 0,
                'total' => 0,
                'note' => 'Seeded job order record #' . $i,
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'deposit_received' => 0,
                'amount_paid' => 0,
                'outstanding_balance' => 0,
                'payment_status' => $paymentStatuses[array_rand($paymentStatuses)],
                'progress' => rand(0, 100),
                'converted_to_invoice' => rand(0, 1) === 1,
                'invoice_reference' => rand(0, 1) === 1 ? 'INV-' . date('Y') . '-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT) : null,
            ]);

            $subTotal = 0;
            $detailCount = rand(3, 5);

            for ($j = 1; $j <= $detailCount; $j++) {
                $quantity = rand(1, 8);
                $unitPrice = rand(80, 500);
                $taxPercent = rand(0, 15);

                $detail = JobOrderDetail::create([
                    'job_order_id' => $jobOrder->id,
                    'item_name' => $items[array_rand($items)],
                    'item_description' => 'Seed detail item ' . $j . ' for job order ' . $i,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'sale_tax_percentage' => $taxPercent,
                ]);

                $subTotal += (float) $detail->total;
            }

            $tax = round($subTotal * 0.05, 2);
            $discount = round($subTotal * 0.03, 2);
            $total = round($subTotal + $tax - $discount, 2);
            $amountPaid = round($total * (rand(0, 100) / 100), 2);
            $paymentStatus = $amountPaid <= 0
                ? 1
                : ($amountPaid >= $total ? 3 : 2);

            $jobOrder->update([
                'sub_total' => $subTotal,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'deposit_received' => round($total * 0.2, 2),
                'amount_paid' => $amountPaid,
                'outstanding_balance' => max($total - $amountPaid, 0),
                'payment_status' => $paymentStatus,
            ]);
        }
    }
}
