<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\VoidRequest;
use App\Models\InventoryLog;
use App\Models\VoidLog;
use App\Models\Category;

class VoidWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_request_void_for_own_sale()
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $sale = Sale::create(['user_id' => $cashier->id, 'total_amount' => 100, 'amount_paid' => 100, 'payment_method' => 'cash']);

        $response = $this->actingAs($cashier)->post(route('transactions.void.request', $sale->id), [
            'reason' => 'Customer change of mind'
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('void_requests', [
            'sale_id' => $sale->id,
            'status' => 'pending'
        ]);

        $this->assertDatabaseHas('void_logs', [
            'sale_id' => $sale->id,
            'action' => 'requested'
        ]);
    }

    public function test_cashier_cannot_request_void_for_others_sale()
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $owner = User::factory()->create(['role' => 'cashier']);
        $sale = Sale::create(['user_id' => $owner->id, 'total_amount' => 50, 'amount_paid' => 50, 'payment_method' => 'cash']);

        $response = $this->actingAs($cashier)->post(route('transactions.void.request', $sale->id), [
            'reason' => 'Wrong payment'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('void');
        $this->assertDatabaseMissing('void_requests', ['sale_id' => $sale->id]);
    }

    public function test_manager_can_approve_void_and_restore_stock_and_mark_sale_void()
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $cashier = User::factory()->create(['role' => 'cashier']);

        $cat = Category::create(['name' => 'General']);
        $product = Product::create(['sku' => 'P-1', 'name' => 'Test Product', 'category_id' => $cat->id, 'stock' => 3, 'selling_price' => 100]);

        $sale = Sale::create(['user_id' => $cashier->id, 'total_amount' => 200, 'amount_paid' => 200, 'payment_method' => 'cash']);
        $item = SaleItem::create(['sale_id' => $sale->id, 'product_id' => $product->id, 'quantity' => 2, 'price' => 100, 'subtotal' => 200]);

        // Ensure pre-condition: product stock is 3 (post-sale reduced already)
        $this->assertEquals(3, $product->fresh()->stock);

        $response = $this->actingAs($manager)->post(route('transactions.void.approve', $sale->id), [
            'void_reason' => 'Duplicate transaction'
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'is_void' => 1,
        ]);

        // product stock should be incremented by 2
        $this->assertEquals(5, $product->fresh()->stock);

        $this->assertDatabaseHas('inventory_logs', [
            'product_id' => $product->id,
            'type' => 'adjustment',
            'notes' => 'void'
        ]);

        $this->assertDatabaseHas('void_logs', [
            'sale_id' => $sale->id,
            'action' => 'approved'
        ]);
    }

    public function test_manager_can_reject_void_request()
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $cashier = User::factory()->create(['role' => 'cashier']);

        $sale = Sale::create(['user_id' => $cashier->id, 'total_amount' => 80, 'amount_paid' => 80, 'payment_method' => 'cash']);
        $vr = VoidRequest::create(['sale_id' => $sale->id, 'requested_by' => $cashier->id, 'requested_at' => now(), 'reason' => 'testing', 'status' => 'pending']);

        $response = $this->actingAs($manager)->post(route('transactions.void.reject', $sale->id), [
            'note' => 'Not valid'
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('void_requests', ['id' => $vr->id, 'status' => 'rejected']);
        $this->assertDatabaseHas('void_logs', ['sale_id' => $sale->id, 'action' => 'rejected']);
    }

    public function test_manager_can_restore_voided_transaction_when_stock_sufficient()
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $cashier = User::factory()->create(['role' => 'cashier']);

        $cat = Category::create(['name' => 'General']);
        $product = Product::create(['sku' => 'P-2', 'name' => 'Restore Product', 'category_id' => $cat->id, 'stock' => 10, 'selling_price' => 50]);

        $sale = Sale::create(['user_id' => $cashier->id, 'total_amount' => 100, 'amount_paid' => 100, 'payment_method' => 'cash', 'is_void' => true, 'voided_by' => $manager->id, 'voided_at' => now(), 'void_reason' => 'Test void']);
        $item = SaleItem::create(['sale_id' => $sale->id, 'product_id' => $product->id, 'quantity' => 2, 'price' => 50, 'subtotal' => 100]);

        // Because sale is voided, stock currently includes the void's restored quantity; we will decrement during restore
        $this->assertEquals(10, $product->fresh()->stock);

        $response = $this->actingAs($manager)->post(route('transactions.void.restore', $sale->id), ['note' => 'Restoring test']);
        $response->assertStatus(302);

        $this->assertDatabaseHas('sales', ['id' => $sale->id, 'is_void' => 0]);
        $this->assertEquals(8, $product->fresh()->stock);
        $this->assertDatabaseHas('void_logs', ['sale_id' => $sale->id, 'action' => 'restored']);
    }

    public function test_restore_fails_when_insufficient_stock()
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $cashier = User::factory()->create(['role' => 'cashier']);

        $cat = Category::create(['name' => 'General']);
        $product = Product::create(['sku' => 'P-3', 'name' => 'Low Stock Product', 'category_id' => $cat->id, 'stock' => 1, 'selling_price' => 200]);

        $sale = Sale::create(['user_id' => $cashier->id, 'total_amount' => 400, 'amount_paid' => 400, 'payment_method' => 'cash', 'is_void' => true, 'voided_by' => $manager->id, 'voided_at' => now(), 'void_reason' => 'Test void']);
        $item = SaleItem::create(['sale_id' => $sale->id, 'product_id' => $product->id, 'quantity' => 2, 'price' => 200, 'subtotal' => 400]);

        $response = $this->actingAs($manager)->post(route('transactions.void.restore', $sale->id), ['note' => 'Attempt restore']);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('void');
        $this->assertDatabaseHas('sales', ['id' => $sale->id, 'is_void' => 1]);
    }
}
