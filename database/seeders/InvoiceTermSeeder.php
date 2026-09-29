<?php

namespace Database\Seeders;

use App\Models\InvoiceTerm;
use App\Models\AccountSetup;
use Illuminate\Database\Seeder;

class InvoiceTermSeeder extends Seeder
{
    public function run(): void
    {
        $projects = AccountSetup::query()->get(['id', 'user_id']);

        if ($projects->isEmpty()) {
            $this->command?->warn('Payment terms were not seeded because no account setup projects were found.');
            return;
        }

        $invoiceTerms = [
            ['term_name' => 'Net 15', 'description' => 'Payment due within 15 days', 'note' => 'Standard short-term payment terms', 'status' => 1],
            ['term_name' => 'Net 30', 'description' => 'Payment due within 30 days', 'note' => 'Most common payment terms', 'status' => 1],
            ['term_name' => 'Net 45', 'description' => 'Payment due within 45 days', 'note' => 'Extended payment terms', 'status' => 1],
            ['term_name' => 'Net 60', 'description' => 'Payment due within 60 days', 'note' => 'Longer payment terms for established clients', 'status' => 1],
            ['term_name' => 'Net 90', 'description' => 'Payment due within 90 days', 'note' => 'Extended credit terms', 'status' => 1],
            ['term_name' => 'Immediate', 'description' => 'Payment due upon receipt', 'note' => 'Payment required immediately', 'status' => 1],
            ['term_name' => 'Due on Receipt', 'description' => 'Payment due on invoice receipt', 'note' => 'Same-day payment required', 'status' => 1],
            ['term_name' => 'End of Month', 'description' => 'Payment due at end of month', 'note' => 'Billing cycle payment terms', 'status' => 1],
            ['term_name' => '2/10 Net 30', 'description' => '2% discount if paid within 10 days, otherwise due in 30 days', 'note' => 'Early payment discount available', 'status' => 1],
            ['term_name' => 'Invoiced', 'description' => 'Payment due per specific invoice terms', 'note' => 'Terms defined per invoice', 'status' => 1],
        ];

        $currentYear = date('Y');
        $prefix = 'TERM';

        foreach ($projects as $project) {
            foreach ($invoiceTerms as $index => $data) {
                $termId = sprintf('%s-%d-%03d', $prefix, $currentYear, $index + 1);

                InvoiceTerm::updateOrCreate(
                    ['project_id' => $project->id, 'term_id' => $termId],
                    array_merge($data, [
                        'inserted_by' => $project->user_id,
                        'updated_by' => $project->user_id,
                        'is_deleted' => 0,
                    ])
                );
            }
        }

        $this->command?->info("Seeded payment terms for {$projects->count()} account setup project(s).");
    }
}
