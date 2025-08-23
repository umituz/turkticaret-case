<?php

namespace Tests\Traits;

use Mockery;
use Mockery\MockInterface;
use Carbon\Carbon;

/**
 * Trait providing common mocking utilities for resource unit tests
 */
trait ResourceMocksTrait
{
    /**
     * Create a mock model with specified attributes
     */
    protected function createMockModel(array $attributes = []): MockInterface
    {
        $model = Mockery::mock();
        
        // Set default timestamps if not provided (but respect explicit null values)
        $now = Carbon::now();
        if (!array_key_exists('created_at', $attributes)) {
            $attributes['created_at'] = $now;
        }
        if (!array_key_exists('updated_at', $attributes)) {
            $attributes['updated_at'] = $now;
        }
        $attributes['uuid'] = $attributes['uuid'] ?? $this->generateTestUuid();
        
        // Set up attribute access
        foreach ($attributes as $key => $value) {
            $model->shouldReceive('getAttribute')->with($key)->andReturn($value);
            $model->$key = $value;
        }
        
        // Set up magic property access
        $model->shouldReceive('__get')->andReturnUsing(function ($key) use ($attributes) {
            return $attributes[$key] ?? null;
        });
        
        // Mock isset calls for conditional attributes
        $model->shouldReceive('__isset')->andReturnUsing(function ($key) use ($attributes) {
            return isset($attributes[$key]);
        });
        
        // Mock relationLoaded for whenLoaded functionality  
        // Only return true if the relation is explicitly set and not null
        $model->shouldReceive('relationLoaded')->andReturnUsing(function ($relation) use ($attributes) {
            return array_key_exists($relation, $attributes) && $attributes[$relation] !== null;
        });
        
        // Mock common relation checks that might not be explicitly set
        $commonRelations = ['currency', 'users', 'category', 'country', 'product', 'items', 'orders'];
        foreach ($commonRelations as $relation) {
            if (!array_key_exists($relation, $attributes)) {
                $model->shouldReceive('relationLoaded')->with($relation)->andReturn(false);
            }
        }
        
        // Mock getRelation for loaded relations
        $model->shouldReceive('getRelation')->andReturnUsing(function ($relation) use ($attributes) {
            return $attributes[$relation] ?? null;
        });
        
        
        // Mock Spatie Media Library methods
        $defaultImageUrl = $attributes['image_path'] ?? null;
        $model->shouldReceive('getFirstMediaUrl')->andReturn($defaultImageUrl);
        $model->shouldReceive('getFirstMediaUrl')->with('images')->andReturn($defaultImageUrl);
        $model->shouldReceive('hasMedia')->andReturn(!empty($defaultImageUrl));
        $model->shouldReceive('getMedia')->andReturn(collect());
        
        // Mock additional common model methods
        $model->shouldReceive('isRTL')->andReturn(false);
        
        return $model;
    }

    /**
     * Create a mock model with loaded relations
     */
    protected function createMockModelWithRelations(array $attributes, array $relations = []): MockInterface
    {
        $model = $this->createMockModel($attributes);
        
        foreach ($relations as $relationName => $relationData) {
            $relatedModel = is_array($relationData) ? $this->createMockModel($relationData) : $relationData;
            
            $model->shouldReceive('relationLoaded')->with($relationName)->andReturn(true);
            $model->shouldReceive('getRelation')->with($relationName)->andReturn($relatedModel);
            $model->$relationName = $relatedModel;
        }
        
        return $model;
    }

    /**
     * Create test data for different resource scenarios
     */
    protected function createResourceTestData(array $overrides = []): array
    {
        $defaults = [
            'uuid' => $this->generateTestUuid(),
            'name' => 'Test Resource',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        return array_merge($defaults, $overrides);
    }

    /**
     * Create mock Carbon date
     */
    protected function createMockDate(string $dateString = null): Carbon
    {
        $date = $dateString ? Carbon::parse($dateString) : Carbon::now();
        
        $mockDate = Mockery::mock(Carbon::class);
        $mockDate->shouldReceive('toIso8601String')->andReturn($date->toIso8601String());
        $mockDate->shouldReceive('format')->andReturnUsing(function ($format) use ($date) {
            return $date->format($format);
        });
        $mockDate->shouldReceive('__toString')->andReturn($date->toIso8601String());
        
        return $mockDate;
    }

    /**
     * Create mock collection for resource collections
     */
    protected function createMockCollection(array $items = []): MockInterface
    {
        $collection = Mockery::mock();
        
        $collection->shouldReceive('all')->andReturn($items);
        $collection->shouldReceive('count')->andReturn(count($items));
        $collection->shouldReceive('isEmpty')->andReturn(empty($items));
        $collection->shouldReceive('isNotEmpty')->andReturn(!empty($items));
        
        // Mock iteration
        $collection->shouldReceive('getIterator')->andReturn(new \ArrayIterator($items));
        
        // Mock sum method for cart/order calculations
        $collection->shouldReceive('sum')->andReturnUsing(function ($field) use ($items) {
            $sum = 0;
            foreach ($items as $item) {
                if (is_array($item) && isset($item[$field])) {
                    $sum += $item[$field];
                } elseif (is_object($item) && isset($item->$field)) {
                    $sum += $item->$field;
                } elseif (is_object($item) && method_exists($item, 'getAttribute')) {
                    $value = $item->getAttribute($field);
                    if ($value !== null) {
                        $sum += $value;
                    }
                }
            }
            return $sum;
        });
        
        return $collection;
    }

    /**
     * Create test UUID for resources
     */
    protected function generateResourceTestUuid(): string
    {
        return $this->generateTestUuid();
    }

    /**
     * Create mock model with minimal data
     */
    protected function createMinimalMockModel(): MockInterface
    {
        return $this->createMockModel([
            'uuid' => $this->generateTestUuid(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    /**
     * Create mock model with all nullable fields as null
     */
    protected function createMockModelWithNulls(array $requiredFields = []): MockInterface
    {
        $attributes = [];
        
        // Set required fields
        foreach ($requiredFields as $field => $value) {
            $attributes[$field] = $value;
        }
        
        // Ensure essential fields are present
        $attributes['uuid'] = $attributes['uuid'] ?? $this->generateTestUuid();
        $attributes['created_at'] = $attributes['created_at'] ?? Carbon::now();
        $attributes['updated_at'] = $attributes['updated_at'] ?? Carbon::now();
        
        return $this->createMockModel($attributes);
    }

    /**
     * Create mock paginated collection that works with Laravel ResourceCollection
     */
    protected function createMockPaginatedCollection(array $items = [], int $total = null)
    {
        $total = $total ?? count($items);
        $perPage = 15;
        $currentPage = 1;
        
        // Use real LengthAwarePaginator - this is the most reliable solution
        // Create a real paginator with the items we want to test
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            collect($items), // items for current page
            $total,          // total items
            $perPage,       // items per page
            $currentPage,   // current page
            [
                'path' => '/test',
                'pageName' => 'page',
            ]
        );
        
        // Add missing methods that Resource Collections expect
        if (method_exists($paginator, 'total')) {
            // LengthAwarePaginator already has total() method
        }
        
        return $paginator;
    }

    /**
     * Create test data for different entity types
     */
    protected function createTestEntityData(string $entityType): array
    {
        $baseData = [
            'uuid' => $this->generateTestUuid(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        switch ($entityType) {
            case 'user':
                return array_merge($baseData, [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                ]);

            case 'product':
                return array_merge($baseData, [
                    'name' => 'Test Product',
                    'slug' => 'test-product',
                    'description' => 'Test Description',
                    'sku' => 'TEST-SKU',
                    'price' => 1000,
                    'stock_quantity' => 50,
                    'image_path' => null,
                    'is_active' => true,
                    'category_uuid' => $this->generateTestUuid(),
                ]);

            case 'category':
                return array_merge($baseData, [
                    'name' => 'Test Category',
                    'description' => 'Test Category Description',
                ]);

            case 'order':
                return array_merge($baseData, [
                    'user_uuid' => $this->generateTestUuid(),
                    'status' => 'pending',
                    'total_amount' => 5000,
                    'shipping_address' => 'Test Address',
                    'notes' => 'Test Notes',
                ]);

            case 'cart':
                return array_merge($baseData, [
                    'user_uuid' => $this->generateTestUuid(),
                ]);

            default:
                return $baseData;
        }
    }
}