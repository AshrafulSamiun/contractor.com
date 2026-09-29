<?php

namespace Database\Seeders;

use App\Models\Seller;
use App\Models\AccountSetup;
use Illuminate\Database\Seeder;

class SellerSeeder extends Seeder
{
    public function run(): void
    {
        $projects = AccountSetup::query()
            ->join('users', 'users.id', '=', 'account_setups.user_id')
            ->where('users.is_active', true)
            ->where('users.role', '!=', 'super_admin')
            ->get([
                'account_setups.id as project_id',
                'account_setups.user_id as owner_user_id',
            ]);

        if ($projects->isEmpty()) {
            $this->command?->warn('Sellers were not seeded because no active account setup projects were found.');
            return;
        }

        $sellers = [
            ['seller_name' => 'ABC Traders', 'email' => 'orders@abctraders.example', 'phone' => '+12125550101', 'website' => 'https://abctraders.example', 'is_active' => true, 'notes' => 'General construction materials and site supplies.'],
            ['seller_name' => 'BuildWell Ltd.', 'email' => 'sales@buildwell.example', 'phone' => '+14165550102', 'website' => 'https://buildwell.example', 'is_active' => true, 'notes' => 'Building materials and finishing products.'],
            ['seller_name' => 'Prime Supplies', 'email' => 'purchasing@primesupplies.example', 'phone' => '+16045550103', 'website' => 'https://primesupplies.example', 'is_active' => true, 'notes' => 'Industrial and commercial supplies.'],
            ['seller_name' => 'Industrial Hub', 'email' => 'quotes@industrialhub.example', 'phone' => '+13125550104', 'website' => 'https://industrialhub.example', 'is_active' => true, 'notes' => 'Tools, machinery, and safety equipment.'],
            ['seller_name' => 'Mega Materials', 'email' => 'orders@megamaterials.example', 'phone' => '+17135550105', 'website' => 'https://megamaterials.example', 'is_active' => true, 'notes' => 'Bulk materials and project delivery.'],
            ['seller_name' => 'Steel & More', 'email' => 'sales@steelandmore.example', 'phone' => '+19055550106', 'website' => 'https://steelandmore.example', 'is_active' => true, 'notes' => 'Steel, fasteners, and fabricated components.'],
            ['seller_name' => 'Quality Goods', 'email' => 'service@qualitygoods.example', 'phone' => '+16175550107', 'website' => 'https://qualitygoods.example', 'is_active' => true, 'notes' => 'General merchandise and consumables.'],
            ['seller_name' => 'Infra Solutions', 'email' => 'bids@infrasolutions.example', 'phone' => '+12065550108', 'website' => 'https://infrasolutions.example', 'is_active' => true, 'notes' => 'Infrastructure and civil project supplies.'],
            ['seller_name' => 'Global Traders', 'email' => 'orders@globaltraders.example', 'phone' => '+13055550109', 'website' => 'https://globaltraders.example', 'is_active' => true, 'notes' => 'Imported materials and specialty products.'],
            ['seller_name' => 'Nova Builders', 'email' => 'sales@novabuilders.example', 'phone' => '+15145550110', 'website' => 'https://novabuilders.example', 'is_active' => true, 'notes' => 'Construction materials and subcontract services.'],
            ['seller_name' => 'Elite Enterprises', 'email' => 'contact@eliteenterprises.example', 'phone' => '+16475550111', 'website' => 'https://eliteenterprises.example', 'is_active' => true, 'notes' => 'Premium fixtures and commercial equipment.'],
            ['seller_name' => 'Ascent Supplies', 'email' => 'orders@ascentsupplies.example', 'phone' => '+14035550112', 'website' => 'https://ascentsupplies.example', 'is_active' => true, 'notes' => 'Electrical, plumbing, and HVAC supplies.'],
            ['seller_name' => 'Pro Build Co.', 'email' => 'sales@probuild.example', 'phone' => '+12155550113', 'website' => 'https://probuild.example', 'is_active' => true, 'notes' => 'Professional-grade construction products.'],
            ['seller_name' => 'Rapid Supplies', 'email' => 'dispatch@rapidsupplies.example', 'phone' => '+16135550114', 'website' => 'https://rapidsupplies.example', 'is_active' => true, 'notes' => 'Same-day and urgent project supplies.'],
            ['seller_name' => 'Maxx Traders', 'email' => 'orders@maxxtraders.example', 'phone' => '+12895550115', 'website' => 'https://maxxtraders.example', 'is_active' => true, 'notes' => 'Tools, hardware, and building consumables.'],
        ];

        $contacts = [
            'John Smith', 'Emily Brown', 'Michael Lee', 'Sarah Wilson', 'David Clark',
            'James Miller', 'Olivia Davis', 'Daniel Moore', 'Sophia Taylor', 'Robert Anderson',
            'Emma Thomas', 'William Jackson', 'Ava White', 'Henry Harris', 'Mia Martin',
        ];

        foreach ($projects as $project) {
            foreach ($sellers as $index => $seller) {
                Seller::updateOrCreate(
                    [
                        'project_id' => $project->project_id,
                        'seller_name' => $seller['seller_name'],
                    ],
                    $seller + [
                        'contact_person' => $contacts[$index],
                        'address' => (100 + $index).' Commerce Street, Toronto, ON, Canada',
                        'tax_number' => 'TAX-'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                        'vendor_category' => $index % 2 === 0 ? 'Construction Supplier' : 'Trade Services',
                        'user_id' => $project->owner_user_id,
                    ]
                );
            }
        }

        $this->command?->info(sprintf(
            'Seeded %d sellers for %d account setup project(s).',
            count($sellers),
            $projects->count()
        ));

        $unassigned = Seller::query()->whereNull('project_id')->count();
        if ($unassigned > 0) {
            $this->command?->warn("{$unassigned} seller record(s) could not be linked to an account setup project.");
        } else {
            $this->command?->info('Verified: every seller has a project ID.');
        }
    }
}
