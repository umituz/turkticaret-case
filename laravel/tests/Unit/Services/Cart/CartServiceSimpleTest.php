<?php

namespace Tests\Unit\Services\Cart;

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
class CartServiceSimpleTest extends BaseServiceUnitTest
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
        $cart = Mockery::mock(Cart::class);
        
        $cart->shouldReceive('load')->with(Mockery::any())->andReturnSelf();

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
        $newCart = Mockery::mock(Cart::class);
        
        $newCart->shouldReceive('load')->with(Mockery::any())->andReturnSelf();

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
    public function it_handles_service_instantiation(): void
    {
        $this->assertInstanceOf(CartService::class, $this->service);
    }

    #[Test]
    public function it_has_cart_repository_dependency(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('cartRepository');
        $property->setAccessible(true);
        
        $this->assertSame($this->cartRepository, $property->getValue($this->service));
    }

    #[Test]
    public function it_has_product_repository_dependency(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('productRepository');
        $property->setAccessible(true);
        
        $this->assertSame($this->productRepository, $property->getValue($this->service));
    }

    #[Test]
    public function it_validates_constructor_parameters(): void
    {
        $reflection = new \ReflectionClass(CartService::class);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull($constructor);
        
        $parameters = $constructor->getParameters();
        $this->assertCount(2, $parameters);
        
        $this->assertEquals('cartRepository', $parameters[0]->getName());
        $this->assertEquals('productRepository', $parameters[1]->getName());
    }
}