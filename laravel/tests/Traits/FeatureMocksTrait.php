<?php

namespace Tests\Traits;

use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Models\Category\Category;
use App\Models\Country\Country;
use App\Models\Currency\Currency;
use App\Models\Language\Language;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Product\Product;
use App\Models\User\User;

/**
 * Trait providing mock data creation utilities for feature tests
 */
trait FeatureMocksTrait
{
    /**
     * Create a test user with specified attributes
     */
    protected function createTestUser(array $attributes = []): User
    {
        if (!isset($attributes['language_uuid'])) {
            $language = Language::where('code', 'en')->first()
                ?? Language::factory()->english()->active()->create();
            $attributes['language_uuid'] = $language->uuid;
        }

        if (!isset($attributes['country_uuid'])) {
            $currency = Currency::where('code', 'USD')->first()
                ?? Currency::factory()->create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'decimals' => 2]);

            $country = Country::where('code', 'US')->first()
                ?? Country::factory()->withCurrency($currency)->create(['code' => 'US', 'name' => 'United States']);

            $attributes['country_uuid'] = $country->uuid;
        }

        $defaults = [
            'name' => 'Test User',
            'email' => 'user' . time() . rand(1000, 9999) . '@example.com',
            'password' => 'password123',
            'email_verified_at' => now(),
        ];

        return User::factory()->create(array_merge($defaults, $attributes));
    }

    /**
     * Create an admin user
     */
    protected function createAdminUser(array $attributes = []): User
    {
        $user = $this->createTestUser(array_merge([
            'email' => 'admin_' . time() . '@turkticaret.test',
            'name' => 'Admin User',
        ], $attributes));

        // Create Admin role if it doesn't exist
        $adminRole = \App\Models\Authority\Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $user->assignRole($adminRole);

        return $user;
    }

    /**
     * Create multiple test users
     */
    protected function createMultipleUsers(int $count = 3, array $attributes = []): \Illuminate\Database\Eloquent\Collection
    {
        return User::factory()->count($count)->create($attributes);
    }

    /**
     * Create a test category
     */
    protected function createTestCategory(array $attributes = []): Category
    {
        $defaults = [
            'name' => 'Test Category ' . time() . rand(1000, 9999),
            'description' => 'A test category description',
            'is_active' => true,
        ];

        return Category::factory()->create(array_merge($defaults, $attributes));
    }

    /**
     * Create multiple test categories
     */
    protected function createMultipleCategories(int $count = 3, array $attributes = []): \Illuminate\Database\Eloquent\Collection
    {
        return Category::factory()->count($count)->create($attributes);
    }

    /**
     * Create a test product
     */
    protected function createTestProduct(array $attributes = []): Product
    {
        $category = $attributes['category_uuid'] ?? $this->createTestCategory()->uuid;

        $defaults = [
            'name' => 'Test Product ' . time() . rand(1000, 9999),
            'description' => 'A test product description',
            'sku' => 'SKU-' . time() . '-' . rand(1000, 9999),
            'price' => 1500, // Price in cents
            'stock_quantity' => 10,
            'category_uuid' => $category,
            'is_active' => true,
        ];

        return Product::factory()->create(array_merge($defaults, $attributes));
    }

    /**
     * Create multiple test products
     */
    protected function createMultipleProducts(int $count = 3, array $attributes = []): \Illuminate\Database\Eloquent\Collection
    {
        return Product::factory()->count($count)->create($attributes);
    }

    /**
     * Create a test cart for user
     */
    protected function createTestCart(User $user = null, array $attributes = []): Cart
    {
        $user = $user ?? $this->createTestUser();

        $defaults = [
            'user_uuid' => $user->uuid,
        ];

        return Cart::factory()->create(array_merge($defaults, $attributes));
    }

    /**
     * Create a test cart item
     */
    protected function createTestCartItem(Cart $cart = null, Product $product = null, array $attributes = []): CartItem
    {
        $cart = $cart ?? $this->createTestCart();
        $product = $product ?? $this->createTestProduct();

        $defaults = [
            'cart_uuid' => $cart->uuid,
            'product_uuid' => $product->uuid,
            'quantity' => 2,
            'unit_price' => $product->price,
        ];

        return CartItem::factory()->create(array_merge($defaults, $attributes));
    }

    /**
     * Create a cart with items
     */
    protected function createCartWithItems(User $user = null, int $itemCount = 3): Cart
    {
        $cart = $this->createTestCart($user);

        for ($i = 0; $i < $itemCount; $i++) {
            $this->createTestCartItem($cart);
        }

        return $cart->fresh();
    }

    /**
     * Create a test order
     */
    protected function createTestOrder(User $user = null, array $attributes = []): Order
    {
        $user = $user ?? $this->createTestUser();

        $defaults = [
            'user_uuid' => $user->uuid,
            'total_amount' => 25000, // Amount in cents
            'status' => 'pending',
            'shipping_address' => '123 Test Street, Test City',
        ];

        return Order::factory()->create(array_merge($defaults, $attributes));
    }

    /**
     * Create a test order item
     */
    protected function createTestOrderItem(Order $order = null, Product $product = null, array $attributes = []): OrderItem
    {
        $order = $order ?? $this->createTestOrder();
        $product = $product ?? $this->createTestProduct();

        $defaults = [
            'order_uuid' => $order->uuid,
            'product_uuid' => $product->uuid,
            'quantity' => 2,
            'unit_price' => $product->price,
        ];

        return OrderItem::factory()->create(array_merge($defaults, $attributes));
    }

    /**
     * Create an order with items
     */
    protected function createOrderWithItems(User $user = null, int $itemCount = 3): Order
    {
        $order = $this->createTestOrder($user);

        $totalAmount = 0;
        for ($i = 0; $i < $itemCount; $i++) {
            $orderItem = $this->createTestOrderItem($order);
            $totalAmount += $orderItem->quantity * $orderItem->unit_price;
        }

        $order->update(['total_amount' => $totalAmount]);
        return $order->fresh();
    }

    /**
     * Create valid request data for user registration
     */
    protected function createValidRegistrationData(array $overrides = []): array
    {
        $defaults = [
            'name' => 'Test User',
            'email' => 'test' . time() . '@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'country_code' => 'US', // Add default country code
        ];

        return array_merge($defaults, $overrides);
    }

    /**
     * Create valid request data for user login
     */
    protected function createValidLoginData(User $user = null, array $overrides = []): array
    {
        $user = $user ?? $this->createTestUser();

        $defaults = [
            'email' => $user->email,
            'password' => 'password123',
        ];

        return array_merge($defaults, $overrides);
    }

    /**
     * Create valid request data for category
     */
    protected function createValidCategoryData(array $overrides = []): array
    {
        $defaults = [
            'name' => 'Test Category ' . time(),
            'description' => 'A test category description',
            'is_active' => true,
        ];

        return array_merge($defaults, $overrides);
    }

    /**
     * Create valid request data for product
     */
    protected function createValidProductData(Category $category = null, array $overrides = []): array
    {
        $category = $category ?? $this->createTestCategory();

        $defaults = [
            'name' => 'Test Product ' . time(),
            'description' => 'A test product description',
            'sku' => 'SKU-' . time() . '-' . rand(1000, 9999),
            'price' => 1500,
            'stock_quantity' => 10,
            'category_uuid' => $category->uuid,
            'is_active' => true,
        ];

        return array_merge($defaults, $overrides);
    }

    /**
     * Create valid request data for adding item to cart
     */
    protected function createValidCartItemData(Product $product = null, array $overrides = []): array
    {
        $product = $product ?? $this->createTestProduct();

        $defaults = [
            'product_uuid' => $product->uuid,
            'quantity' => 2,
        ];

        return array_merge($defaults, $overrides);
    }

    /**
     * Create valid request data for order placement
     */
    protected function createValidOrderData(array $overrides = []): array
    {
        $defaults = [
            'shipping_address' => '123 Test Street, Test City',
            'notes' => 'Test order notes',
        ];

        return array_merge($defaults, $overrides);
    }

    /**
     * Create a test country
     */
    protected function createTestCountry(array $attributes = []): Country
    {
        static $testCounter = 0;
        $testCounter++;

        $codeChar = chr(65 + ($testCounter % 26)); // A-Z

        // Create currency if not provided
        if (!isset($attributes['currency_uuid'])) {
            $currency = Currency::factory()->create();
            $attributes['currency_uuid'] = $currency->uuid;
        }

        $defaults = [
            'code' => $codeChar . chr(65 + (intval($testCounter / 26) % 26)),
            'name' => 'Test Country ' . $testCounter,
            'is_active' => true,
        ];

        return Country::factory()->create(array_merge($defaults, $attributes));
    }

    /**
     * Create multiple test countries
     */
    protected function createMultipleCountries(int $count = 3, array $attributes = []): \Illuminate\Database\Eloquent\Collection
    {
        return Country::factory()->count($count)->create($attributes);
    }

    /**
     * Create valid request data for country
     */
    protected function createValidCountryData(array $overrides = []): array
    {
        static $dataCounter = 0;
        $dataCounter++;

        $codeChar = chr(68 + ($dataCounter % 22)); // D-Z (avoiding conflicts)

        // Create currency if not provided
        if (!isset($overrides['currency_uuid'])) {
            $currency = Currency::factory()->create();
            $overrides['currency_uuid'] = $currency->uuid;
        }

        $defaults = [
            'code' => $codeChar . chr(65 + (intval($dataCounter / 22) % 26)),
            'name' => 'Data Country ' . $dataCounter,
            'is_active' => true,
        ];

        return array_merge($defaults, $overrides);
    }

    /**
     * Create a test currency
     */
    protected function createTestCurrency(array $attributes = []): Currency
    {
        static $testCounter = 0;
        $testCounter++;

        $defaults = [
            'code' => 'TC' . $testCounter,
            'name' => 'Test Currency ' . $testCounter,
            'symbol' => 'TC' . $testCounter,
            'decimals' => 2,
            'is_active' => true,
        ];

        return Currency::factory()->create(array_merge($defaults, $attributes));
    }

    /**
     * Create multiple test currencies
     */
    protected function createMultipleCurrencies(int $count = 3, array $attributes = []): \Illuminate\Database\Eloquent\Collection
    {
        return Currency::factory()->count($count)->create($attributes);
    }

    /**
     * Create valid request data for currency
     */
    protected function createValidCurrencyData(array $overrides = []): array
    {
        static $dataCounter = 0;
        $dataCounter++;

        $defaults = [
            'code' => 'TST' . $dataCounter,
            'name' => 'Test Currency ' . $dataCounter,
            'symbol' => '₸' . $dataCounter,
            'decimals' => 2,
            'is_active' => true,
        ];

        return array_merge($defaults, $overrides);
    }
}
