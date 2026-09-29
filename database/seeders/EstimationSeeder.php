<?php

namespace Database\Seeders;

use App\Models\AccountHolder;
use App\Models\Estimation;
use App\Models\JobSite;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EstimationSeeder extends Seeder
{
    public function run(): void
    {
        $customers = AccountHolder::query()
            ->where('account_type', 1)
            ->where('is_deleted', false)
            ->where('status_active', true)
            ->orderBy('project_id')
            ->orderBy('id')
            ->get();

        if ($customers->isEmpty()) {
            $this->command?->warn('No active customers found. Run CustomerProfileSeeder first.');

            return;
        }

        $statuses = [2, 1, 2, 4, 3, 1, 2, 1];
        $services = [
            ['Concrete Work (Foundation)', 'Foundation preparation and concrete work', 1, 12500],
            ['Masonry Work', 'Block and masonry installation', 1, 8750],
            ['Electrical Installation', 'Electrical rough-in and installation', 1, 6500],
            ['Plumbing Work', 'Plumbing supply and installation', 1, 4250],
            ['Painting Work', 'Interior preparation and painting', 1, 3250],
        ];

        foreach ($customers->take(15)->values() as $index => $customer) {
            $issueDate = Carbon::today()->subDays(5 + ($index * 3));
            $selectedServices = array_slice($services, 0, 2 + ($index % 4));
            $subTotal = collect($selectedServices)->sum(fn (array $service) => $service[2] * $service[3]);
            $tax = round($subTotal * 0.05, 2);
            $number = sprintf('EST-SEED-%d-%04d', $customer->project_id, $index + 1);
            $jobSite = JobSite::query()
                ->where('project_id', $customer->project_id)
                ->where('is_deleted', false)
                ->where('status', 1)
                ->first();

            $estimation = Estimation::updateOrCreate(
                ['estimation_no' => $number],
                [
                    'issue_date' => $issueDate,
                    'expire_date' => $issueDate->copy()->addDays(30),
                    'currency' => 1,
                    'status' => $statuses[$index % count($statuses)],
                    'job_type' => ($index % 6) + 1,
                    'job_description' => 'Sales estimate for '.($customer->company_name ?: $customer->account_name),
                    'customer_id' => $customer->id,
                    'job_site_id' => $jobSite?->id,
                    'schedule_start_date' => $issueDate->copy()->addDays(7),
                    'schedule_end_date' => $issueDate->copy()->addDays(21),
                    'scope_of_work' => 'Supply labor, materials, supervision, and site cleanup for the quoted work.',
                    'sub_total' => $subTotal,
                    'tax' => $tax,
                    'discount' => 0,
                    'total' => $subTotal + $tax,
                    'note' => 'Pricing is valid until the expiry date shown on this estimate.',
                    'payment_method' => 4,
                    'deposit_required' => true,
                    'deposit_percentage' => 30,
                    'deposit_amount' => round($subTotal * 0.30, 2),
                    'payment_term' => $customer->payment_terms ?: 'Net 30',
                    'tax_rate' => 5,
                    'tax_registration_no' => $customer->tax_id_no,
                    'notes_to_customer' => 'Please review the scope and contact us with any requested changes.',
                ]
            );

            $estimation->details()->delete();
            foreach ($selectedServices as [$name, $description, $quantity, $unitPrice]) {
                $estimation->details()->create([
                    'item_name' => $name,
                    'item_description' => $description,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'sale_tax_percentage' => 5,
                ]);
            }
        }

        $this->command?->info('Seeded '.min(15, $customers->count()).' sales estimates.');
    }
}
