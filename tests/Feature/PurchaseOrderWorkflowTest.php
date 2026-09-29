<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\PurchaseOrderController;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PurchaseOrderWorkflowTest extends TestCase
{
    private User $owner;
    private Seller $seller;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'po_test', 'database.connections.po_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        DB::purge('po_test');
        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('email'); $table->timestamps();
        });
        Schema::create('account_setups', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id');
        });
        Schema::create('sellers', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('project_id'); $table->unsignedBigInteger('user_id');
            $table->string('seller_name'); $table->string('contact_person')->nullable();
            $table->string('phone')->nullable(); $table->string('email')->nullable(); $table->string('website')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2026_08_30_000004_create_purchase_orders_table.php'))->up();
        (require database_path('migrations/2026_09_19_000005_complete_purchase_order_workflow.php'))->up();
        Route::middleware(SubstituteBindings::class)->group(function () {
            Route::apiResource('test-purchase-orders', PurchaseOrderController::class)->parameters(['test-purchase-orders' => 'purchaseOrder']);
            Route::post('test-purchase-orders/{purchaseOrder}/workflow', [PurchaseOrderController::class, 'workflow']);
            Route::post('test-purchase-orders/{purchaseOrder}/documents', [PurchaseOrderController::class, 'addDocument']);
            Route::post('test-purchase-orders/{purchaseOrder}/attachments', [PurchaseOrderController::class, 'attach']);
            Route::get('test-purchase-orders/{purchaseOrder}/attachments/{attachment}', [PurchaseOrderController::class, 'download']);
        });
        $this->owner = User::create(['name' => 'Owner', 'email' => 'owner@example.test']);
        DB::table('account_setups')->insert(['id' => 17, 'user_id' => $this->owner->id]);
        $this->seller = Seller::create(['project_id' => 17, 'user_id' => $this->owner->id, 'seller_name' => 'Test Seller', 'contact_person' => 'Contact Name', 'email' => 'seller@example.test', 'phone' => '+12125550100']);
        $this->actingAs($this->owner);
    }

    private function payload(array $override = []): array
    {
        return array_replace([
            'seller_id' => $this->seller->id, 'seller_name' => 'Untrusted name', 'po_date' => now()->toDateString(),
            'currency_code' => 'USD', 'status' => 'Pending', 'payment_term' => 'Net 30',
            'items' => [['name' => 'Pipe', 'code' => 'PVC-1', 'uom' => 'FT', 'quantity' => 3, 'cost_rate' => 12.345, 'sales_tax' => 2.96]],
            'requester_details' => ['phone' => '5551234', 'company_name' => 'Buyer Company'],
            'delivery_details' => ['contact_person' => 'Warehouse', 'phone' => '5559999'],
        ], $override);
    }

    private function createOrder(array $override = []): array
    {
        return $this->postJson('/test-purchase-orders', $this->payload($override))->assertCreated()->json('data');
    }

    public function test_totals_and_seller_snapshot_are_calculated_and_saved(): void
    {
        $order = $this->createOrder(['total' => 1, 'subtotal' => 1]);
        $this->assertSame('40.00', $order['total']);
        $this->assertSame('37.04', $order['subtotal']);
        $this->assertSame('Contact Name', $order['seller_details']['contact_person']);
        $this->assertSame('Test Seller', $order['seller_name']);
        $this->assertSame('Warehouse', $order['delivery_details']['contact_person']);
        $this->assertSame('Buyer Company', $order['requester_details']['company_name']);
        $this->assertSame(17, $order['project_id']);
        $this->putJson('/test-purchase-orders/'.$order['id'], $this->payload(['po_no' => $order['po_no'], 'notes' => 'Gate 2']))->assertOk()->assertJsonPath('data.notes', 'Gate 2');
    }

    public function test_approval_conversion_and_payments_have_audits_and_cannot_repeat(): void
    {
        $order = $this->createOrder(); $url = '/test-purchase-orders/'.$order['id'];
        $this->postJson($url.'/workflow', ['action' => 'convert'])->assertUnprocessable();
        $this->postJson($url.'/workflow', ['action' => 'submit'])->assertOk()->assertJsonPath('data.approval_status', 'Submitted');
        $this->postJson($url.'/workflow', ['action' => 'approve'])->assertOk()->assertJsonPath('data.status', 'Accepted');
        $converted = $this->postJson($url.'/workflow', ['action' => 'convert'])->assertOk()->json('data');
        $this->assertCount(1, $converted['documents']);
        $this->assertSame('40.00', $converted['documents'][0]['total']);
        $this->assertCount(4, $converted['activities']);
        $this->postJson($url.'/workflow', ['action' => 'convert'])->assertUnprocessable();
        $this->putJson($url, $this->payload(['po_no' => $order['po_no']]))->assertUnprocessable();
        $this->deleteJson($url)->assertUnprocessable();
        $payment = ['type' => 'Bill Payment', 'number' => 'PAY-1', 'date' => now()->toDateString(), 'subtotal' => 50, 'sales_tax' => 0];
        $this->postJson($url.'/documents', $payment)->assertUnprocessable();
        $payment['subtotal'] = 20;
        $this->postJson($url.'/documents', $payment)->assertOk()->assertJsonPath('data.payment_status', 'Partially Paid');
        $this->postJson($url.'/documents', $payment)->assertUnprocessable();
        $payment = array_replace($payment, ['number' => 'PAY-2', 'subtotal' => 17.04, 'sales_tax' => 2.96]);
        $this->postJson($url.'/documents', $payment)->assertOk()->assertJsonPath('data.payment_status', 'Paid');
    }

    public function test_cross_account_access_and_foreign_sellers_are_rejected(): void
    {
        $order = $this->createOrder();
        $other = User::create(['name' => 'Other', 'email' => 'other@example.test']);
        DB::table('account_setups')->insert(['id' => 29, 'user_id' => $other->id]);
        $this->actingAs($other);
        $this->getJson('/test-purchase-orders')->assertOk()->assertJsonPath('data.total', 0);
        $this->getJson('/test-purchase-orders/'.$order['id'])->assertNotFound();
        $this->postJson('/test-purchase-orders/'.$order['id'].'/workflow', ['action' => 'approve'])->assertNotFound();
        $this->postJson('/test-purchase-orders', $this->payload())->assertUnprocessable()->assertJsonValidationErrors('seller_id');
        DB::table('account_setups')->where('id', 29)->delete();
        $this->getJson('/test-purchase-orders')->assertNotFound();
    }

    public function test_numbers_do_not_collide_after_deleting_a_previous_order(): void
    {
        $first = $this->createOrder(); $second = $this->createOrder();
        $this->deleteJson('/test-purchase-orders/'.$first['id'])->assertOk();
        $third = $this->createOrder();
        $this->assertNotSame($second['po_no'], $third['po_no']);
    }

    public function test_attachments_are_private_and_downloadable_by_the_owner(): void
    {
        Storage::fake('local');
        $order = $this->createOrder(); $url = '/test-purchase-orders/'.$order['id'];
        $result = $this->post($url.'/attachments', ['file' => UploadedFile::fake()->createWithContent('specifications.txt', 'Delivery at gate 2')], ['Accept' => 'application/json'])->assertOk()->json('data');
        $id = $result['attachments'][0]['id'];
        $this->assertArrayNotHasKey('path', $result['attachments'][0]);
        $this->get($url.'/attachments/'.$id)->assertDownload('specifications.txt');
        $other = User::create(['name' => 'Other', 'email' => 'other@example.test']);
        DB::table('account_setups')->insert(['id' => 29, 'user_id' => $other->id]);
        $this->actingAs($other)->get($url.'/attachments/'.$id)->assertNotFound();
    }

    public function test_rejected_and_expired_orders_cannot_be_converted(): void
    {
        $order = $this->createOrder(); $url = '/test-purchase-orders/'.$order['id'];
        $this->postJson($url.'/workflow', ['action' => 'reject'])->assertOk()->assertJsonPath('data.approval_status', 'Rejected');
        $this->postJson($url.'/workflow', ['action' => 'convert'])->assertUnprocessable();
        $this->postJson($url.'/workflow', ['action' => 'approve'])->assertUnprocessable();
        $this->postJson('/test-purchase-orders', $this->payload(['status' => 'Accepted']))->assertUnprocessable();
    }
}
