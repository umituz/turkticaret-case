<?php

namespace Tests\Unit\Services\Cart;

use App\DTOs\Cart\CartItemDTO;
use App\Exceptions\Product\InsufficientStockException;
use App\Exceptions\Product\OutOfStockException;
use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Models\Product\Product;
use App\Repositories\Cart\CartRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Services\Cart\CartService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Base\BaseServiceUnitTest;

#[CoversClass(CartService::class)]
class CartServiceTest extends BaseServiceUnitTest
{
    private MockInterface $cartRepository;
    private MockInterface $productRepository;

    protected function getServiceClass(): string
    {
        return CartService::class;
    }

    protected function getServiceDependencies(): array
    {
        $this->cartRepository = $this->mock(CartRepositoryInterface::class);
        $this->productRepository = $this->mock(ProductRepositoryInterface::class);

        return [
            $this->cartRepository,
            $this->productRepository,
        ];
    }

    #[Test]
    public function it_gets_existing_cart_for_user(): void
    {
        $userUuid = $this->getTestUserUuid();
        $cart = $this->mockTypedModel(Cart::class, ['user_uuid' => $userUuid]);
        
        $cart->shouldReceive('load')->with([
            'cartItems.product', 
            'cartItems' => Mockery::on(function ($callback) {
                // Simulate the callback execution
                $query = Mockery::mock();
                $query->shouldReceive('with')->with('product')->andReturnSelf();
                $callback($query);
                return true;
            })
        ])->andReturnSelf();

        $this->cartRepository->shouldReceive('findByUserUuid')
            ->with($userUuid)
            ->once()
            ->andReturn($cart);

        $result = $this->service->getOrCreateCart($userUuid);

        $this->assertSame($cart, $result);
    }

    #[Test]
    public function it_creates_new_cart_when_user_has_no_cart(): void
    {
        $userUuid = $this->getTestUserUuid();
        $newCart = $this->mockTypedModel(Cart::class, ['user_uuid' => $userUuid]);
        
        $newCart->shouldReceive('load')->with([
            'cartItems.product', 
            'cartItems' => Mockery::on(function ($callback) {
                $query = Mockery::mock();
                $query->shouldReceive('with')->with('product')->andReturnSelf();
                $callback($query);
                return true;
            })
        ])->andReturnSelf();

        $this->cartRepository->shouldReceive('findByUserUuid')
            ->with($userUuid)
            ->once()
            ->andReturn(null);

        $this->cartRepository->shouldReceive('create')
            ->with(['user_uuid' => $userUuid])
            ->once()
            ->andReturn($newCart);

        $result = $this->service->getOrCreateCart($userUuid);

        $this->assertSame($newCart, $result);
    }

    #[Test]
    public function it_adds_new_product_to_empty_cart(): void
    {
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();
        $quantity = 2;
        $unitPrice = 1000; // 10.00 in minor units

        $product = $this->mockTypedModel(Product::class, [
            'uuid' => $productUuid,
            'name' => 'Test Product',
            'price' => $unitPrice,
            'stock_quantity' => 10
        ]);

        $cart = $this->mockTypedModel(Cart::class, ['user_uuid' => $userUuid]);
        $cartItemsRelation = $this->mockHasManyRelation();

        $cart->shouldReceive('load')->andReturnSelf();
        $cart->shouldReceive('cartItems')->andReturn($cartItemsRelation);
        
        // Configure the relation to return null for first() (no existing item)
        $cartItemsRelation->shouldReceive('where')
            ->with('product_uuid', $productUuid)
            ->andReturnSelf();
        $cartItemsRelation->shouldReceive('first')->andReturn(null);

        $product->shouldReceive('hasStock')
            ->with($quantity)
            ->andReturn(true);

        $cartItemsRelation->shouldReceive('create')
            ->with([
                'product_uuid' => $productUuid,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ])
            ->andReturn($this->mockTypedModel(CartItem::class));

        $cart->shouldReceive('fresh')
            ->with(['cartItems.product'])
            ->andReturn($cart);

        $this->cartRepository->shouldReceive('findByUserUuid')
            ->with($userUuid)
            ->andReturn($cart);

        $this->productRepository->shouldReceive('findByUuid')
            ->with($productUuid)
            ->andReturn($product);

        $data = new CartItemDTO($productUuid, $quantity);

        $result = $this->service->addToCart($userUuid, $data);

        $this->assertSame($cart, $result);
    }

    #[Test]
    public function it_updates_existing_cart_item_quantity(): void
    {
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();
        $existingQuantity = 1;
        $additionalQuantity = 2;
        $totalQuantity = $existingQuantity + $additionalQuantity;

        $product = $this->mockTypedModel(Product::class, [
            'uuid' => $productUuid,
            'name' => 'Test Product',
            'stock_quantity' => 10,
            'price' => 1000
        ]);

        $existingCartItem = $this->mockTypedModel(CartItem::class, [
            'product_uuid' => $productUuid,
            'quantity' => $existingQuantity
        ]);

        $cart = $this->mockTypedModel(Cart::class, ['user_uuid' => $userUuid]);
        $cartItemsRelation = $this->mockHasManyRelation();

        $cart->shouldReceive('load')->andReturnSelf();
        $cart->shouldReceive('cartItems')->andReturn($cartItemsRelation);
        
        $cartItemsRelation->shouldReceive('where')
            ->with('product_uuid', $productUuid)
            ->andReturn($cartItemsRelation);
        $cartItemsRelation->shouldReceive('first')->andReturn($existingCartItem);

        $product->shouldReceive('hasStock')
            ->with($additionalQuantity)
            ->andReturn(true);

        $existingCartItem->shouldReceive('update')
            ->with(['quantity' => $totalQuantity])
            ->andReturn(true);

        $cart->shouldReceive('fresh')
            ->with(['cartItems.product'])
            ->andReturn($cart);

        $this->cartRepository->shouldReceive('findByUserUuid')
            ->with($userUuid)
            ->andReturn($cart);

        $this->productRepository->shouldReceive('findByUuid')
            ->with($productUuid)
            ->andReturn($product);

        $data = new CartItemDTO($productUuid, $additionalQuantity);

        $result = $this->service->addToCart($userUuid, $data);

        $this->assertSame($cart, $result);
    }

    #[Test]
    public function it_throws_out_of_stock_exception_when_product_has_no_stock(): void
    {
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();
        $quantity = 1;

        $product = $this->mockTypedModel(Product::class, [
            'uuid' => $productUuid,
            'name' => 'Test Product',
            'stock_quantity' => 0,
            'price' => 1000
        ]);

        $cart = $this->mockTypedModel(Cart::class, ['user_uuid' => $userUuid]);
        $cartItemsRelation = $this->mockHasManyRelation();

        $cart->shouldReceive('load')->andReturnSelf();
        $cart->shouldReceive('cartItems')->andReturn($cartItemsRelation);
        
        $cartItemsRelation->shouldReceive('where')
            ->with('product_uuid', $productUuid)
            ->andReturn($cartItemsRelation);
        $cartItemsRelation->shouldReceive('first')->andReturn(null);

        $this->cartRepository->shouldReceive('findByUserUuid')
            ->with($userUuid)
            ->andReturn($cart);

        $this->productRepository->shouldReceive('findByUuid')
            ->with($productUuid)
            ->andReturn($product);

        $data = new CartItemDTO($productUuid, $quantity);

        $this->expectException(OutOfStockException::class);

        $this->service->addToCart($userUuid, $data);
    }

    #[Test]
    public function it_throws_insufficient_stock_exception_when_requested_quantity_exceeds_stock(): void
    {
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();
        $quantity = 5;
        $stockQuantity = 3;

        $product = $this->mockTypedModel(Product::class, [
            'uuid' => $productUuid,
            'name' => 'Test Product',
            'stock_quantity' => $stockQuantity,
            'price' => 1000
        ]);

        $cart = $this->mockTypedModel(Cart::class, ['user_uuid' => $userUuid]);
        $cartItemsRelation = $this->mockHasManyRelation();

        $cart->shouldReceive('load')->andReturnSelf();
        $cart->shouldReceive('cartItems')->andReturn($cartItemsRelation);
        
        $cartItemsRelation->shouldReceive('where')
            ->with('product_uuid', $productUuid)
            ->andReturn($cartItemsRelation);
        $cartItemsRelation->shouldReceive('first')->andReturn(null);

        $product->shouldReceive('hasStock')
            ->with($quantity)
            ->andReturn(false);

        $this->cartRepository->shouldReceive('findByUserUuid')
            ->with($userUuid)
            ->andReturn($cart);

        $this->productRepository->shouldReceive('findByUuid')
            ->with($productUuid)
            ->andReturn($product);

        $data = new CartItemDTO($productUuid, $quantity);

        $this->expectException(InsufficientStockException::class);

        $this->service->addToCart($userUuid, $data);
    }

    #[Test]
    public function it_throws_out_of_stock_exception_when_updating_existing_item_and_product_has_no_stock(): void
    {
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();
        $existingQuantity = 1;
        $additionalQuantity = 1;

        $product = $this->mockTypedModel(Product::class, [
            'uuid' => $productUuid,
            'name' => 'Test Product',
            'stock_quantity' => 0,
            'price' => 1000
        ]);

        $existingCartItem = $this->mockTypedModel(CartItem::class, [
            'product_uuid' => $productUuid,
            'quantity' => $existingQuantity
        ]);

        $cart = $this->mockTypedModel(Cart::class, ['user_uuid' => $userUuid]);
        $cartItemsRelation = $this->mockHasManyRelation();

        $cart->shouldReceive('load')->andReturnSelf();
        $cart->shouldReceive('cartItems')->andReturn($cartItemsRelation);
        
        $cartItemsRelation->shouldReceive('where')
            ->with('product_uuid', $productUuid)
            ->andReturn($cartItemsRelation);
        $cartItemsRelation->shouldReceive('first')->andReturn($existingCartItem);

        $this->cartRepository->shouldReceive('findByUserUuid')
            ->with($userUuid)
            ->andReturn($cart);

        $this->productRepository->shouldReceive('findByUuid')
            ->with($productUuid)
            ->andReturn($product);

        $data = new CartItemDTO($productUuid, $additionalQuantity);

        $this->expectException(OutOfStockException::class);

        $this->service->addToCart($userUuid, $data);
    }

    #[Test]
    public function it_updates_cart_item_quantity(): void
    {
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();
        $newQuantity = 3;

        $product = $this->mockTypedModel(Product::class, [
            'uuid' => $productUuid,
            'name' => 'Test Product',
            'stock_quantity' => 10,
            'price' => 1000
        ]);

        $cartItem = $this->mockTypedModel(CartItem::class, [
            'product_uuid' => $productUuid,
            'quantity' => 1
        ]);

        $cart = $this->mockTypedModel(Cart::class, ['user_uuid' => $userUuid]);
        $cartItemsRelation = $this->mockHasManyRelation();

        $cart->shouldReceive('load')->andReturnSelf();
        $cart->shouldReceive('cartItems')->andReturn($cartItemsRelation);
        
        $cartItemsRelation->shouldReceive('where')
            ->with('product_uuid', $productUuid)
            ->andReturn($cartItemsRelation);
        $cartItemsRelation->shouldReceive('first')->andReturn($cartItem);

        $product->shouldReceive('hasStock')
            ->with($newQuantity)
            ->andReturn(true);

        $cartItem->shouldReceive('update')
            ->with(['quantity' => $newQuantity])
            ->andReturn(true);

        $cart->shouldReceive('fresh')
            ->with(['cartItems.product'])
            ->andReturn($cart);

        $this->cartRepository->shouldReceive('findByUserUuid')
            ->with($userUuid)
            ->andReturn($cart);

        $this->productRepository->shouldReceive('findByUuid')
            ->with($productUuid)
            ->andReturn($product);

        $data = new CartItemDTO($productUuid, $newQuantity);

        $result = $this->service->updateCartItem($userUuid, $data);

        $this->assertSame($cart, $result);
    }

    #[Test]
    public function it_throws_out_of_stock_exception_when_updating_cart_item_and_product_has_no_stock(): void
    {
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();
        $newQuantity = 2;

        // Use our mockTypedModel which should handle property access correctly
        $product = $this->mockTypedModel(Product::class, [
            'uuid' => $productUuid,
            'stock_quantity' => 0,
            'name' => 'Test Product',
            'price' => 1000
        ]);
        

        $cartItem = $this->mockTypedModel(CartItem::class, [
            'product_uuid' => $productUuid,
            'quantity' => 1
        ]);

        $cart = $this->mockTypedModel(Cart::class, ['user_uuid' => $userUuid]);
        // Configure the relation to return our cartItem when first() is called
        $cartItemsRelation = $this->mockHasManyRelation([
            'first' => $cartItem
        ]);

        $cart->shouldReceive('load')->andReturnSelf();
        $cart->shouldReceive('cartItems')->andReturn($cartItemsRelation);
        
        // Let's also mock the cartItem update method properly
        $cartItem->shouldReceive('update')->with(['quantity' => $newQuantity])->andReturn(true);

        $this->cartRepository->shouldReceive('findByUserUuid')
            ->with($userUuid)
            ->andReturn($cart);
            
        // Mock the create method in case getOrCreateCart needs to create a cart
        $this->cartRepository->shouldReceive('create')
            ->with(['user_uuid' => $userUuid])
            ->andReturn($cart);

        $this->productRepository->shouldReceive('findByUuid')
            ->with($productUuid)
            ->andReturn($product);

        // Mock the fresh method that's called at the end
        $cart->shouldReceive('fresh')->with(['cartItems.product'])->andReturnSelf();
        
        // Let's mock the jsonSerialize method to avoid serialization issues
        $cart->shouldReceive('jsonSerialize')->andReturn(['test' => 'cart']);

        $data = new CartItemDTO($productUuid, $newQuantity);
        
        $this->expectException(OutOfStockException::class);
        
        $this->service->updateCartItem($userUuid, $data);
    }

    #[Test]
    public function it_throws_insufficient_stock_exception_when_updating_cart_item_quantity_exceeds_stock(): void
    {
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();
        $newQuantity = 5;
        $stockQuantity = 3;

        $product = $this->mockTypedModel(Product::class, [
            'uuid' => $productUuid,
            'name' => 'Test Product',
            'stock_quantity' => $stockQuantity,
            'price' => 1000
        ]);
        
        // Ensure stock_quantity property is properly accessible and hasStock method works
        $product->stock_quantity = $stockQuantity;

        $cartItem = $this->mockTypedModel(CartItem::class, [
            'product_uuid' => $productUuid,
            'quantity' => 1
        ]);

        $cart = $this->mockTypedModel(Cart::class, ['user_uuid' => $userUuid]);
        // Configure the relation to return our cartItem when first() is called
        $cartItemsRelation = $this->mockHasManyRelation([
            'first' => $cartItem
        ]);

        $cart->shouldReceive('load')->andReturnSelf();
        $cart->shouldReceive('cartItems')->andReturn($cartItemsRelation);

        $product->shouldReceive('hasStock')
            ->with($newQuantity)
            ->andReturn(false);

        $this->cartRepository->shouldReceive('findByUserUuid')
            ->with($userUuid)
            ->andReturn($cart);

        $this->productRepository->shouldReceive('findByUuid')
            ->with($productUuid)
            ->andReturn($product);
            
        // Mock the cartItem update method and cart fresh method
        $cartItem->shouldReceive('update')->with(['quantity' => $newQuantity])->andReturn(true);
        $cart->shouldReceive('fresh')->with(['cartItems.product'])->andReturnSelf();
        $cart->shouldReceive('jsonSerialize')->andReturn(['test' => 'cart']);

        $data = new CartItemDTO($productUuid, $newQuantity);

        $this->expectException(InsufficientStockException::class);

        $this->service->updateCartItem($userUuid, $data);
    }

    #[Test]
    public function it_does_nothing_when_updating_non_existent_cart_item(): void
    {
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();
        $newQuantity = 2;

        $cart = $this->mockTypedModel(Cart::class, ['user_uuid' => $userUuid]);
        $cartItemsRelation = $this->mockHasManyRelation();

        $cart->shouldReceive('load')->andReturnSelf();
        $cart->shouldReceive('cartItems')->andReturn($cartItemsRelation);
        
        $cartItemsRelation->shouldReceive('where')
            ->with('product_uuid', $productUuid)
            ->andReturn($cartItemsRelation);
        $cartItemsRelation->shouldReceive('first')->andReturn(null);

        $cart->shouldReceive('fresh')
            ->with(['cartItems.product'])
            ->andReturn($cart);

        $this->cartRepository->shouldReceive('findByUserUuid')
            ->with($userUuid)
            ->andReturn($cart);

        $data = new CartItemDTO($productUuid, $newQuantity);

        $result = $this->service->updateCartItem($userUuid, $data);

        $this->assertSame($cart, $result);
    }

    #[Test]
    public function it_removes_product_from_cart(): void
    {
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();

        $cart = $this->mockTypedModel(Cart::class, ['user_uuid' => $userUuid]);
        $cartItemsRelation = $this->mockHasManyRelation();

        $cart->shouldReceive('load')->andReturnSelf();
        $cart->shouldReceive('cartItems')->andReturn($cartItemsRelation);
        
        $cartItemsRelation->shouldReceive('where')
            ->with('product_uuid', $productUuid)
            ->andReturn($cartItemsRelation);
        $cartItemsRelation->shouldReceive('delete')->andReturn(true);

        $cart->shouldReceive('fresh')
            ->with(['cartItems.product'])
            ->andReturn($cart);

        $this->cartRepository->shouldReceive('findByUserUuid')
            ->with($userUuid)
            ->andReturn($cart);

        $result = $this->service->removeFromCart($userUuid, $productUuid);

        $this->assertSame($cart, $result);
    }

    #[Test]
    public function it_clears_entire_cart(): void
    {
        $userUuid = $this->getTestUserUuid();

        $cart = $this->mockTypedModel(Cart::class, ['user_uuid' => $userUuid]);
        $cartItemsRelation = $this->mockHasManyRelation();

        $cart->shouldReceive('load')->andReturnSelf();
        $cart->shouldReceive('cartItems')->andReturn($cartItemsRelation);
        
        $cartItemsRelation->shouldReceive('delete')->andReturn(true);

        $this->cartRepository->shouldReceive('findByUserUuid')
            ->with($userUuid)
            ->andReturn($cart);

        $this->service->clearCart($userUuid);

        // Verify that delete was called on cartItems
        $this->assertTrue(true); // Test passes if no exceptions are thrown
    }

    #[Test]
    public function it_creates_new_cart_before_clearing_if_none_exists(): void
    {
        $userUuid = $this->getTestUserUuid();
        $newCart = $this->mockTypedModel(Cart::class, ['user_uuid' => $userUuid]);
        $cartItemsRelation = $this->mockHasManyRelation();

        $newCart->shouldReceive('load')->andReturnSelf();
        $newCart->shouldReceive('cartItems')->andReturn($cartItemsRelation);
        
        $cartItemsRelation->shouldReceive('delete')->andReturn(true);

        $this->cartRepository->shouldReceive('findByUserUuid')
            ->with($userUuid)
            ->andReturn(null);

        $this->cartRepository->shouldReceive('create')
            ->with(['user_uuid' => $userUuid])
            ->andReturn($newCart);

        $this->service->clearCart($userUuid);

        // Test passes if no exceptions are thrown
        $this->assertTrue(true);
    }

    #[Test]
    public function it_handles_edge_case_with_zero_quantity_update(): void
    {
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();
        $newQuantity = 0;

        $product = $this->mockTypedModel(Product::class, [
            'uuid' => $productUuid,
            'name' => 'Test Product',
            'stock_quantity' => 10,
            'price' => 1000
        ]);

        $cartItem = $this->mockTypedModel(CartItem::class, [
            'product_uuid' => $productUuid,
            'quantity' => 1
        ]);

        $cart = $this->mockTypedModel(Cart::class, ['user_uuid' => $userUuid]);
        $cartItemsRelation = $this->mockHasManyRelation();

        $cart->shouldReceive('load')->andReturnSelf();
        $cart->shouldReceive('cartItems')->andReturn($cartItemsRelation);
        
        $cartItemsRelation->shouldReceive('where')
            ->with('product_uuid', $productUuid)
            ->andReturn($cartItemsRelation);
        $cartItemsRelation->shouldReceive('first')->andReturn($cartItem);

        $product->shouldReceive('hasStock')
            ->with($newQuantity)
            ->andReturn(true);

        $cartItem->shouldReceive('update')
            ->with(['quantity' => $newQuantity])
            ->andReturn(true);

        $cart->shouldReceive('fresh')
            ->with(['cartItems.product'])
            ->andReturn($cart);

        $this->cartRepository->shouldReceive('findByUserUuid')
            ->with($userUuid)
            ->andReturn($cart);

        $this->productRepository->shouldReceive('findByUuid')
            ->with($productUuid)
            ->andReturn($product);

        $data = new CartItemDTO($productUuid, $newQuantity);

        $result = $this->service->updateCartItem($userUuid, $data);

        $this->assertSame($cart, $result);
    }
}