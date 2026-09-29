<?php

namespace Database\Seeders;

use App\Models\FixedAsset;
use Illuminate\Database\Seeder;

class FixedAssetSeeder extends Seeder
{
    public function run(): void
    {
        // Match the existing profile seeders; override when seeding another project.
        $projectId = (int) env('SEED_PROJECT_ID', 16);
        $assets = [
            ['asset_no' => 'AST-000123', 'asset_name' => 'Excavator CAT 320', 'asset_group' => 'Heavy Equipment', 'asset_type' => 'Excavator', 'location' => 'Site - 01', 'status' => 'Active', 'purchase_date' => '2024-05-01', 'purchase_cost' => 125000.00, 'book_value' => 113250.00, 'last_valuation_date' => '2024-05-10'],
            ['asset_no' => 'AST-000124', 'asset_name' => 'Toyota Hilux', 'asset_group' => 'Vehicles', 'asset_type' => 'Pickup', 'location' => 'Site - 02', 'status' => 'Active', 'purchase_date' => '2024-04-15', 'purchase_cost' => 31500.00, 'book_value' => 28500.00, 'last_valuation_date' => '2024-05-08'],
            ['asset_no' => 'AST-000125', 'asset_name' => 'Komatsu Bulldozer D65', 'asset_group' => 'Heavy Equipment', 'asset_type' => 'Bulldozer', 'location' => 'Site - 01', 'status' => 'Active', 'purchase_date' => '2024-03-20', 'purchase_cost' => 169000.00, 'book_value' => 152300.00, 'last_valuation_date' => '2024-05-09'],
            ['asset_no' => 'AST-000126', 'asset_name' => 'Generator 100kVA', 'asset_group' => 'Equipment', 'asset_type' => 'Generator', 'location' => 'Head Office', 'status' => 'Active', 'purchase_date' => '2024-02-05', 'purchase_cost' => 20500.00, 'book_value' => 18750.00, 'last_valuation_date' => '2024-05-07'],
            ['asset_no' => 'AST-000127', 'asset_name' => 'Honda Civic', 'asset_group' => 'Vehicles', 'asset_type' => 'Sedan', 'location' => 'Head Office', 'status' => 'Active', 'purchase_date' => '2024-02-12', 'purchase_cost' => 24500.00, 'book_value' => 22000.00, 'last_valuation_date' => '2024-05-06'],
            ['asset_no' => 'AST-000128', 'asset_name' => 'Air Compressor 500L', 'asset_group' => 'Equipment', 'asset_type' => 'Compressor', 'location' => 'Site - 03', 'status' => 'Under Maintenance', 'purchase_date' => '2024-01-10', 'purchase_cost' => 12000.00, 'book_value' => 9800.00, 'last_valuation_date' => '2024-05-05'],
            ['asset_no' => 'AST-000129', 'asset_name' => 'Forklift 3 Ton', 'asset_group' => 'Material Handling', 'asset_type' => 'Forklift', 'location' => 'Warehouse', 'status' => 'Active', 'purchase_date' => '2023-12-18', 'purchase_cost' => 18500.00, 'book_value' => 14500.00, 'last_valuation_date' => '2024-05-04'],
            ['asset_no' => 'AST-000130', 'asset_name' => 'Nissan Urvan', 'asset_group' => 'Vehicles', 'asset_type' => 'Van', 'location' => 'Site - 02', 'status' => 'Active', 'purchase_date' => '2023-11-25', 'purchase_cost' => 32000.00, 'book_value' => 26750.00, 'last_valuation_date' => '2024-05-03'],
            ['asset_no' => 'AST-000131', 'asset_name' => 'Backhoe Loader JCB 3DX', 'asset_group' => 'Heavy Equipment', 'asset_type' => 'Backhoe Loader', 'location' => 'Site - 03', 'status' => 'Disposed', 'purchase_date' => '2023-10-01', 'purchase_cost' => 110000.00, 'book_value' => 0, 'last_valuation_date' => '2024-04-15'],
            ['asset_no' => 'AST-000132', 'asset_name' => 'Office Furniture Set', 'asset_group' => 'Furniture & Fixtures', 'asset_type' => 'Office Furniture', 'location' => 'Head Office', 'status' => 'Active', 'purchase_date' => '2023-09-05', 'purchase_cost' => 15000.00, 'book_value' => 12000.00, 'last_valuation_date' => '2024-05-02'],
        ];

        foreach ($assets as $asset) {
            FixedAsset::withTrashed()->updateOrCreate(
                ['project_id' => $projectId, 'asset_no' => $asset['asset_no']],
                $asset + ['project_id' => $projectId, 'notes' => null, 'created_by' => 0, 'updated_by' => 0, 'deleted_at' => null]
            );
        }
    }
}
