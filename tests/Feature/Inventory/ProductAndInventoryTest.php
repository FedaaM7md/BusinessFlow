<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace Tests\Feature\Inventory;

use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\InventoryService;
use Tests\TestCase;

class ProductAndInventoryTest extends TestCase
{
    public function test_product_can_be_created_updated_and_deleted(): void
    {
        $admin = $this->makeUser('admin');
        $category = Category::create(['name' => 'Furniture']);

        $this->actingAs($admin)->post('/products', [
            'sku' => 'SKU-001',
            'name' => 'Executive Desk',
            'name_ar' => 'مكتب تنفيذي',
            'type' => 'product',
            'category_id' => $category->id,
            'unit' => 'piece',
            'price' => '2500.50',
            'cost' => '1500.25',
            'tax_rate' => '14',
            'is_tracked' => '1',
            'low_stock_threshold' => '4',
            'is_active' => '1',
        ])->assertRedirect();

        $product = Product::query()->where('sku', 'SKU-001')->firstOrFail();
        $this->assertSame(250050, $product->price_minor);
        $this->assertSame(150025, $product->cost_minor);
        $this->assertSame(1400, $product->tax_rate);
        $this->assertTrue($product->is_tracked);

        $this->actingAs($admin)->put('/products/'.$product->id, [
            'sku' => 'SKU-001',
            'name' => 'Updated Desk',
            'type' => 'product',
            'category_id' => $category->id,
            'unit' => 'piece',
            'price' => '2600.00',
            'cost' => '1500.00',
            'tax_rate' => '14',
            'is_tracked' => '1',
            'low_stock_threshold' => '4',
            'is_active' => '1',
        ])->assertRedirect(route('products.show', $product));

        $this->assertSame('Updated Desk', $product->fresh()->name);
        $this->assertSame(260000, $product->fresh()->price_minor);

        $this->actingAs($admin)->delete('/products/'.$product->id)->assertRedirect('/products');
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_product_validation_and_service_tracking_rules(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post('/products', [
            'sku' => '',
            'name' => '',
            'type' => 'service',
            'unit' => '',
            'price' => 'abc',
            'tax_rate' => '101',
            'is_tracked' => '1',
            'is_active' => '1',
        ])->assertSessionHasErrors(['sku', 'name', 'unit', 'price', 'tax_rate']);

        $this->actingAs($admin)->post('/products', [
            'sku' => 'SERV-1',
            'name' => 'Consulting',
            'type' => 'service',
            'unit' => 'hour',
            'price' => '500.00',
            'cost' => '0.00',
            'tax_rate' => '14',
            'is_tracked' => '1',
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertFalse(Product::query()->where('sku', 'SERV-1')->firstOrFail()->is_tracked);
    }

    public function test_categories_can_be_managed_and_protected_when_used(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post('/categories', ['name' => 'Paper', 'name_ar' => 'ورق'])
            ->assertRedirect('/categories');
        $category = Category::query()->where('name', 'Paper')->firstOrFail();

        $this->makeProduct(['category_id' => $category->id]);
        $this->actingAs($admin)->delete('/categories/'.$category->id)->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_warehouses_can_be_created_and_default_is_unique(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post('/warehouses', [
            'code' => 'WH-A', 'name' => 'Main', 'location' => 'Cairo', 'is_default' => '1', 'is_active' => '1',
        ])->assertRedirect('/warehouses');
        $first = Warehouse::query()->where('code', 'WH-A')->firstOrFail();
        $this->assertTrue($first->is_default);

        $this->actingAs($admin)->post('/warehouses', [
            'code' => 'WH-B', 'name' => 'Branch', 'is_default' => '1', 'is_active' => '1',
        ])->assertRedirect('/warehouses');

        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue(Warehouse::query()->where('code', 'WH-B')->firstOrFail()->is_default);

        $this->actingAs($admin)->post('/warehouses', ['code' => '', 'name' => ''])
            ->assertSessionHasErrors(['code', 'name']);
    }

    public function test_stock_in_out_adjustment_and_transfer_update_on_hand(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct();
        $main = $this->makeWarehouse(['code' => 'MAIN', 'is_default' => true]);
        $second = $this->makeWarehouse(['code' => 'SECOND', 'is_default' => false]);

        $this->actingAs($admin)->post('/inventory', [
            'type' => 'in', 'product_id' => $product->id, 'warehouse_id' => $main->id,
            'quantity' => '10', 'unit_cost' => '50.00', 'moved_at' => now()->format('Y-m-d'),
        ])->assertRedirect('/inventory');
        $this->assertEquals(10, $product->onHand());

        $this->actingAs($admin)->post('/inventory', [
            'type' => 'out', 'product_id' => $product->id, 'warehouse_id' => $main->id,
            'quantity' => '3', 'moved_at' => now()->format('Y-m-d'),
        ])->assertRedirect('/inventory');
        $this->assertEquals(7, $product->onHand());

        $this->actingAs($admin)->post('/inventory', [
            'type' => 'transfer', 'product_id' => $product->id, 'warehouse_id' => $main->id,
            'target_warehouse_id' => $second->id, 'quantity' => '2', 'moved_at' => now()->format('Y-m-d'),
        ])->assertRedirect('/inventory');
        $this->assertEquals(5, $product->onHand($main->id));
        $this->assertEquals(2, $product->onHand($second->id));

        $this->actingAs($admin)->post('/inventory', [
            'type' => 'adjustment', 'product_id' => $product->id, 'warehouse_id' => $main->id,
            'quantity' => '-1', 'moved_at' => now()->format('Y-m-d'),
        ])->assertRedirect('/inventory');
        $this->assertEquals(4, $product->onHand($main->id));

        $this->actingAs($admin)->post('/inventory', [
            'type' => 'out', 'product_id' => $product->id, 'warehouse_id' => $main->id,
            'quantity' => '100', 'moved_at' => now()->format('Y-m-d'),
        ])->assertSessionHasErrors('stock');

        $this->assertEquals(6, StockMovement::query()->where('product_id', $product->id)->sum('quantity'));
    }

    public function test_low_stock_detection_and_page(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct(['name' => 'Low Item', 'low_stock_threshold' => 5]);
        $warehouse = $this->makeWarehouse();
        app(InventoryService::class)->stockIn($product, $warehouse, 3, $admin, 100, null, 'test');

        $this->assertTrue(Product::lowStock()->whereKey($product->id)->exists());

        $this->actingAs($admin)->get('/inventory/low-stock')
            ->assertOk()
            ->assertSee('Low Item');
    }
}
