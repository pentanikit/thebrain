<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItems;

// Adjust these if your cart models have different names
use App\Models\Cart;
use App\Models\CartItem;

// Adjust controller namespace
use App\Http\Controllers\OrderController;

class OrderStoreInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Minimal routes needed for redirects in your controller
        Route::get('/cart', fn () => 'cart')->name('cart.showcart');
        Route::get('/thanks/{order_number}', fn () => 'thanks')->name('orders.thankyou');

        // We'll define the POST route inside each test after binding the controller.
    }

    /** @test */
    public function it_places_order_and_decrements_inventory_then_clears_cart()
    {
        $product = Product::create([
            'category_id'     => null,
            'sub_category_id' => null,
            'child_category_id' => null,
            'name'            => 'Test Product',
            'slug'            => 'test-product',
            'sku'             => 'SKU-1',
            'price'           => 100,
            'old_price'       => null,
            'offer_price'     => 90,
            'stock_quantity'  => 10,
            'stock_status'    => 'in_stock',
            'thumbnail'       => null,
            'is_active'       => 1,
        ]);

        $cart = Cart::create([
            // fill required fields for your carts table
        ]);

        CartItem::create([
            'cart_id'     => $cart->id,
            'product_id'  => $product->id,
            'quantity'    => 2,
            'price'       => null, // let controller choose offer_price/price
        ]);

        // Bind a testable controller that returns our cart
        $this->bindTestableController($cart);

        Route::post('/test/orders', [TestableOrderController::class, 'store'])->name('orders.store');

        $payload = [
            'customer_name'     => 'Mr X',
            'customer_phone'    => '01700000000',
            'customer_email'    => 'x@example.com',
            'shipping_address'  => 'Dhaka',
            'shipping_city'     => 'Dhaka',
            'shipping_postcode' => '1207',
            'notes'             => null,
            'payment_method'    => 'cod',
            'delivery_area'     => 'inside_dhaka',
        ];

        $res = $this->post(route('orders.store'), $payload);

        $res->assertRedirect(); // to thankyou

        // Order created
        $this->assertDatabaseCount('orders', 1);
        $order = Order::first();
        $this->assertNotNull($order);

        // Order item created
        $this->assertDatabaseCount('order_items', 1);
        $item = OrderItems::first();
        $this->assertEquals($product->id, $item->product_id);
        $this->assertEquals(2, (int)$item->quantity);

        // Inventory decremented
        $product->refresh();
        $this->assertEquals(8, (int)$product->stock_quantity);
        $this->assertEquals('in_stock', $product->stock_status);

        // Cart cleared (adjust if you use soft deletes)
        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
    }

    /** @test */
    public function it_rejects_order_when_stock_is_insufficient_and_rolls_back_everything()
    {
        $product = Product::create([
            'category_id'     => null,
            'sub_category_id' => null,
            'child_category_id' => null,
            'name'            => 'Low Stock',
            'slug'            => 'low-stock',
            'sku'             => 'SKU-2',
            'price'           => 100,
            'old_price'       => null,
            'offer_price'     => null,
            'stock_quantity'  => 1,
            'stock_status'    => 'in_stock',
            'thumbnail'       => null,
            'is_active'       => 1,
        ]);

        $cart = Cart::create([
            // fill required fields for your carts table
        ]);

        CartItem::create([
            'cart_id'     => $cart->id,
            'product_id'  => $product->id,
            'quantity'    => 2, // more than stock
            'price'       => null,
        ]);

        $this->bindTestableController($cart);

        Route::post('/test/orders', [TestableOrderController::class, 'store'])->name('orders.store');

        $payload = [
            'customer_name'     => 'Mr Y',
            'customer_phone'    => '01800000000',
            'shipping_address'  => 'Dhaka',
            'payment_method'    => 'cod',
            'delivery_area'     => 'inside_dhaka',
        ];

        $res = $this->post(route('orders.store'), $payload);

        // On error your code redirects to cart.showcart
        $res->assertRedirect(route('cart.showcart'));
        $res->assertSessionHas('error');

        // Nothing created
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);

        // Stock unchanged
        $product->refresh();
        $this->assertEquals(1, (int)$product->stock_quantity);

        // Cart NOT cleared (since order failed)
        $this->assertDatabaseHas('carts', ['id' => $cart->id]);
        $this->assertDatabaseHas('cart_items', ['cart_id' => $cart->id]);
    }

    private function bindTestableController(Cart $cart): void
    {
        // Bind our controller into Laravel container so route can resolve it
        $this->app->bind(TestableOrderController::class, function () use ($cart) {
            return new TestableOrderController($cart);
        });
    }
}

/**
 * Test-only controller that forces getCurrentCart() to return our known cart,
 * and uses a predictable order number.
 */
class TestableOrderController extends OrderController
{
    public function __construct(private \App\Models\Cart $cart) {}

    protected function getCurrentCart(Request $request)
    {
        // make sure items + product relation are available like the real flow
        return $this->cart->load('items.product');
    }

    protected function generateOrderNumber()
    {
        return 'TEST-ORDER-0001';
    }
}
