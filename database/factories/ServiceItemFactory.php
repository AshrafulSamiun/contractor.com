<?php

namespace Database\Factories;

use App\Models\ServiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceItemFactory extends Factory
{
    protected $model = ServiceItem::class;

    public function definition(): array
    {
        return [
            'project_id' => 1,
            'item_no' => $this->faker->unique()->numerify('SER-2026-####'),
            'item_name' => $this->faker->words(3, true),
            'unit_of_measure' => $this->faker->randomElement(['Per Hour', 'Per Delivery', 'Per Item', 'Per Shipment']),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'sales_tax_applicable' => $this->faker->boolean,
            'status' => $this->faker->randomElement([1, 2]),
            'note' => $this->faker->sentence,
            'inserted_by' => 0,
            'updated_by' => 0,
            'is_deleted' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 1]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 2]);
    }

    public function taxable(): static
    {
        return $this->state(fn (array $attributes) => ['sales_tax_applicable' => true]);
    }
}
