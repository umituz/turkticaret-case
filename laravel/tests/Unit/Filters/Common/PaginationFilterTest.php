<?php

namespace Tests\Unit\Filters\Common;

use App\Filters\Common\PaginationFilter;
use App\Filters\Base\FilterEnums;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Mockery;

/**
 * Unit tests for PaginationFilter
 * Tests pagination parameter handling with query builder mocking
 */
#[CoversClass(PaginationFilter::class)]
#[Group('unit')]
#[Group('filters')]
#[Small]
class PaginationFilterTest extends UnitTestCase
{
    #[Test]
    public function handle_applies_default_pagination_when_no_params(): void
    {
        // Arrange
        $data = [];
        $filter = new PaginationFilter($data);
        
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('setAttribute')
            ->with('paginationData', [
                FilterEnums::PARAM_PAGE => 1,
                FilterEnums::PARAM_PER_PAGE => FilterEnums::DEFAULT_PER_PAGE
            ])
            ->once();
        
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('getModel')
            ->once()
            ->andReturn($model);
        
        $next = fn($builder) => $builder;

        // Act
        $result = $filter->handle($builder, $next);

        // Assert
        $this->assertSame($builder, $result);
    }

    #[Test]
    public function handle_applies_custom_page_and_per_page(): void
    {
        // Arrange
        $data = [
            FilterEnums::PARAM_PAGE => 3,
            FilterEnums::PARAM_PER_PAGE => 20
        ];
        $filter = new PaginationFilter($data);
        
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('setAttribute')
            ->with('paginationData', [
                FilterEnums::PARAM_PAGE => 3,
                FilterEnums::PARAM_PER_PAGE => 20
            ])
            ->once();
        
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('getModel')
            ->once()
            ->andReturn($model);
        
        $next = fn($builder) => $builder;

        // Act
        $result = $filter->handle($builder, $next);

        // Assert
        $this->assertSame($builder, $result);
    }

    #[Test]
    public function handle_enforces_max_per_page_limit(): void
    {
        // Arrange
        $data = [
            FilterEnums::PARAM_PAGE => 1,
            FilterEnums::PARAM_PER_PAGE => 9999 // Over the limit
        ];
        $filter = new PaginationFilter($data);
        
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('setAttribute')
            ->with('paginationData', [
                FilterEnums::PARAM_PAGE => 1,
                FilterEnums::PARAM_PER_PAGE => FilterEnums::MAX_PER_PAGE
            ])
            ->once();
        
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('getModel')
            ->once()
            ->andReturn($model);
        
        $next = fn($builder) => $builder;

        // Act
        $result = $filter->handle($builder, $next);

        // Assert
        $this->assertSame($builder, $result);
    }

    #[Test]
    public function handle_corrects_invalid_page_numbers(): void
    {
        // Arrange
        $data = [
            FilterEnums::PARAM_PAGE => 0, // Invalid page
            FilterEnums::PARAM_PER_PAGE => 15
        ];
        $filter = new PaginationFilter($data);
        
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('setAttribute')
            ->with('paginationData', [
                FilterEnums::PARAM_PAGE => 1, // Corrected to 1
                FilterEnums::PARAM_PER_PAGE => 15
            ])
            ->once();
        
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('getModel')
            ->once()
            ->andReturn($model);
        
        $next = fn($builder) => $builder;

        // Act
        $result = $filter->handle($builder, $next);

        // Assert
        $this->assertSame($builder, $result);
    }

    #[Test]
    public function handle_corrects_negative_page_numbers(): void
    {
        // Arrange
        $data = [
            FilterEnums::PARAM_PAGE => -5, // Negative page
            FilterEnums::PARAM_PER_PAGE => 10
        ];
        $filter = new PaginationFilter($data);
        
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('setAttribute')
            ->with('paginationData', [
                FilterEnums::PARAM_PAGE => 1, // Corrected to 1
                FilterEnums::PARAM_PER_PAGE => 10
            ])
            ->once();
        
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('getModel')
            ->once()
            ->andReturn($model);
        
        $next = fn($builder) => $builder;

        // Act
        $result = $filter->handle($builder, $next);

        // Assert
        $this->assertSame($builder, $result);
    }

    #[Test]
    public function handle_converts_string_values_to_integers(): void
    {
        // Arrange
        $data = [
            FilterEnums::PARAM_PAGE => '5', // String
            FilterEnums::PARAM_PER_PAGE => '25' // String
        ];
        $filter = new PaginationFilter($data);
        
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('setAttribute')
            ->with('paginationData', [
                FilterEnums::PARAM_PAGE => 5,
                FilterEnums::PARAM_PER_PAGE => 25
            ])
            ->once();
        
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('getModel')
            ->once()
            ->andReturn($model);
        
        $next = fn($builder) => $builder;

        // Act
        $result = $filter->handle($builder, $next);

        // Assert
        $this->assertSame($builder, $result);
    }

    #[Test]
    public function handle_handles_non_numeric_strings_gracefully(): void
    {
        // Arrange
        $data = [
            FilterEnums::PARAM_PAGE => 'invalid',
            FilterEnums::PARAM_PER_PAGE => 'also_invalid'
        ];
        $filter = new PaginationFilter($data);
        
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('setAttribute')
            ->with('paginationData', [
                FilterEnums::PARAM_PAGE => 1, // Default when invalid
                FilterEnums::PARAM_PER_PAGE => FilterEnums::DEFAULT_PER_PAGE // Default when invalid
            ])
            ->once();
        
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('getModel')
            ->once()
            ->andReturn($model);
        
        $next = fn($builder) => $builder;

        // Act
        $result = $filter->handle($builder, $next);

        // Assert
        $this->assertSame($builder, $result);
    }

    #[Test]
    public function handle_calls_next_closure_and_returns_result(): void
    {
        // Arrange
        $data = [];
        $filter = new PaginationFilter($data);
        
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('setAttribute')->once();
        
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('getModel')
            ->once()
            ->andReturn($model);
        
        $modifiedBuilder = Mockery::mock(Builder::class);
        
        $next = function($passedBuilder) use ($builder, $modifiedBuilder) {
            $this->assertSame($builder, $passedBuilder);
            return $modifiedBuilder;
        };

        // Act
        $result = $filter->handle($builder, $next);

        // Assert
        $this->assertSame($modifiedBuilder, $result);
    }

    #[Test]
    public function handle_sets_pagination_data_on_model(): void
    {
        // Arrange
        $data = [
            FilterEnums::PARAM_PAGE => 10,
            FilterEnums::PARAM_PER_PAGE => 50
        ];
        $filter = new PaginationFilter($data);
        
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('setAttribute')
            ->with('paginationData', [
                FilterEnums::PARAM_PAGE => 10,
                FilterEnums::PARAM_PER_PAGE => 50
            ])
            ->once();
        
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('getModel')
            ->once()
            ->andReturn($model);
        
        $next = fn($builder) => $builder;

        // Act
        $result = $filter->handle($builder, $next);

        // Assert
        $this->assertSame($builder, $result);
    }

    #[Test]
    public function handle_works_with_edge_case_values(): void
    {
        // Arrange
        $data = [
            FilterEnums::PARAM_PAGE => FilterEnums::MAX_PER_PAGE, // Very high page
            FilterEnums::PARAM_PER_PAGE => 1 // Minimum per page
        ];
        $filter = new PaginationFilter($data);
        
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('setAttribute')
            ->with('paginationData', [
                FilterEnums::PARAM_PAGE => FilterEnums::MAX_PER_PAGE,
                FilterEnums::PARAM_PER_PAGE => 1
            ])
            ->once();
        
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('getModel')
            ->once()
            ->andReturn($model);
        
        $next = fn($builder) => $builder;

        // Act
        $result = $filter->handle($builder, $next);

        // Assert
        $this->assertSame($builder, $result);
    }
}