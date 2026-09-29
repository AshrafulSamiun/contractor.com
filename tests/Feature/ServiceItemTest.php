<?php

namespace Tests\Feature;

use App\Models\ServiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_service_items(): void
    {
        ServiceItem::factory()->count(5)->create();

        $response = $this->getJson('/api/service-items');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'item_no', 'item_name', 'price', 'status'],
                    ],
                ],
            ]);
    }

    public function test_can_create_service_item(): void
    {
        $payload = [
            'item_name' => 'Test Service',
            'unit_of_measure' => 'Hour',
            'price' => 50.00,
            'sales_tax_applicable' => true,
            'status' => 1,
            'note' => 'Test note',
        ];

        $response = $this->postJson('/api/service-items', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Service item created successfully',
            ]);

        $this->assertDatabaseHas('service_items', [
            'item_name' => 'Test Service',
            'unit_of_measure' => 'Hour',
        ]);
    }

    public function test_create_service_item_validates_required_fields(): void
    {
        $response = $this->postJson('/api/service-items', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['item_name']);
    }

    public function test_can_show_single_service_item(): void
    {
        $item = ServiceItem::factory()->create();

        $response = $this->getJson("/api/service-items/{$item->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $item->id,
                    'item_name' => $item->item_name,
                ],
            ]);
    }

    public function test_can_update_service_item(): void
    {
        $item = ServiceItem::factory()->create();

        $payload = [
            'item_name' => 'Updated Service',
            'price' => 75.00,
        ];

        $response = $this->putJson("/api/service-items/{$item->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Service item updated successfully',
            ]);

        $this->assertDatabaseHas('service_items', [
            'id' => $item->id,
            'item_name' => 'Updated Service',
        ]);
    }

    public function test_can_delete_service_item(): void
    {
        $item = ServiceItem::factory()->create();

        $response = $this->deleteJson("/api/service-items/{$item->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Service item deleted successfully',
            ]);

        $this->assertSoftDeleted('service_items', ['id' => $item->id]);
    }

    public function test_can_toggle_service_item_status(): void
    {
        $item = ServiceItem::factory()->create(['status' => 1]);

        $response = $this->postJson("/api/service-items/{$item->id}/toggle-status");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $item->refresh();
        $this->assertEquals(2, $item->status);
    }

    public function test_can_filter_service_items_by_status(): void
    {
        ServiceItem::factory()->count(3)->create(['status' => 1]);
        ServiceItem::factory()->count(2)->create(['status' => 2]);

        $response = $this->getJson('/api/service-items?status=1');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_can_search_service_items(): void
    {
        ServiceItem::factory()->create(['item_name' => 'Delivery Service']);
        ServiceItem::factory()->create(['item_name' => 'Packaging Service']);

        $response = $this->getJson('/api/service-items?search=Delivery');

        $response->assertStatus(200);
        $this->assertStringContainsString('Delivery', $response->json('data.data.0.item_name'));
    }
}
