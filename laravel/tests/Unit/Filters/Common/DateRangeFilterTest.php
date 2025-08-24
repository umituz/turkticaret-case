<?php

namespace Tests\Unit\Filters\Common;

use App\Filters\Common\DateRangeFilter;
use App\Filters\Base\FilterEnums;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Database\Eloquent\Builder;
use Mockery;

/**
 * Unit tests for DateRangeFilter
 * Tests date range filtering with query builder mocking
 */
#[CoversClass(DateRangeFilter::class)]
#[Group('unit')]
#[Group('filters')]
#[Small]
class DateRangeFilterTest extends UnitTestCase
{
    #[Test]
    public function handle_applies_no_filter_when_no_dates_provided(): void
    {
        // Arrange
        $data = [];
        $filter = new DateRangeFilter($data);
        $builder = Mockery::mock(Builder::class);
        $next = fn($builder) => $builder;

        // Act
        $result = $filter->handle($builder, $next);

        // Assert
        $this->assertSame($builder, $result);
    }

    #[Test]
    public function handle_applies_start_date_filter_when_provided(): void
    {
        // Arrange
        $startDate = '2024-01-01';
        $data = [FilterEnums::PARAM_START_DATE => $startDate];
        $filter = new DateRangeFilter($data);
        
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('whereDate')
            ->once()
            ->with('created_at', '>=', $startDate)
            ->andReturn($builder);
        
        $next = fn($builder) => $builder;

        // Act
        $result = $filter->handle($builder, $next);

        // Assert
        $this->assertSame($builder, $result);
    }

    #[Test]
    public function handle_applies_end_date_filter_when_provided(): void
    {
        // Arrange
        $endDate = '2024-12-31';
        $data = [FilterEnums::PARAM_END_DATE => $endDate];
        $filter = new DateRangeFilter($data);
        
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('whereDate')
            ->once()
            ->with('created_at', '<=', $endDate)
            ->andReturn($builder);
        
        $next = fn($builder) => $builder;

        // Act
        $result = $filter->handle($builder, $next);

        // Assert
        $this->assertSame($builder, $result);
    }

    #[Test]
    public function handle_applies_both_date_filters_when_both_provided(): void
    {
        // Arrange
        $startDate = '2024-01-01';
        $endDate = '2024-12-31';
        $data = [
            FilterEnums::PARAM_START_DATE => $startDate,
            FilterEnums::PARAM_END_DATE => $endDate
        ];
        $filter = new DateRangeFilter($data);
        
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('whereDate')
            ->once()
            ->with('created_at', '>=', $startDate)
            ->andReturn($builder);
        $builder->shouldReceive('whereDate')
            ->once()
            ->with('created_at', '<=', $endDate)
            ->andReturn($builder);
        
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
        $filter = new DateRangeFilter($data);
        $builder = Mockery::mock(Builder::class);
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
    public function handle_ignores_null_start_date(): void
    {
        // Arrange
        $endDate = '2024-12-31';
        $data = [
            FilterEnums::PARAM_START_DATE => null,
            FilterEnums::PARAM_END_DATE => $endDate
        ];
        $filter = new DateRangeFilter($data);
        
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('whereDate')
            ->once()
            ->with('created_at', '<=', $endDate)
            ->andReturn($builder);
        
        $next = fn($builder) => $builder;

        // Act
        $result = $filter->handle($builder, $next);

        // Assert
        $this->assertSame($builder, $result);
    }

    #[Test]
    public function handle_ignores_null_end_date(): void
    {
        // Arrange
        $startDate = '2024-01-01';
        $data = [
            FilterEnums::PARAM_START_DATE => $startDate,
            FilterEnums::PARAM_END_DATE => null
        ];
        $filter = new DateRangeFilter($data);
        
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('whereDate')
            ->once()
            ->with('created_at', '>=', $startDate)
            ->andReturn($builder);
        
        $next = fn($builder) => $builder;

        // Act
        $result = $filter->handle($builder, $next);

        // Assert
        $this->assertSame($builder, $result);
    }

    #[Test]
    public function handle_ignores_empty_string_dates(): void
    {
        // Arrange
        $data = [
            FilterEnums::PARAM_START_DATE => '',
            FilterEnums::PARAM_END_DATE => ''
        ];
        $filter = new DateRangeFilter($data);
        $builder = Mockery::mock(Builder::class);
        $next = fn($builder) => $builder;

        // Act
        $result = $filter->handle($builder, $next);

        // Assert
        $this->assertSame($builder, $result);
    }
}