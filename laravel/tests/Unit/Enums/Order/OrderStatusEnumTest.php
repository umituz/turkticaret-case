<?php

namespace Tests\Unit\Enums\Order;

use App\Enums\Order\OrderStatusEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Base\UnitTestCase;

/**
 * Comprehensive unit tests for OrderStatusEnum
 * Tests all enum cases, helper methods, and state transition logic
 */
#[CoversClass(OrderStatusEnum::class)]
final class OrderStatusEnumTest extends UnitTestCase
{
    #[Test]
    public function it_has_all_expected_enum_cases(): void
    {
        $expectedCases = [
            'PENDING',
            'CONFIRMED', 
            'PROCESSING',
            'SHIPPED',
            'DELIVERED',
            'CANCELLED',
            'REFUNDED'
        ];

        $actualCases = array_map(fn($case) => $case->name, OrderStatusEnum::cases());

        $this->assertCount(count($expectedCases), $actualCases);
        
        foreach ($expectedCases as $expectedCase) {
            $this->assertContains($expectedCase, $actualCases, "Missing enum case: {$expectedCase}");
        }
    }

    #[Test]
    public function it_has_correct_enum_values(): void
    {
        $expectedValues = [
            'PENDING' => 'pending',
            'CONFIRMED' => 'confirmed',
            'PROCESSING' => 'processing',
            'SHIPPED' => 'shipped',
            'DELIVERED' => 'delivered',
            'CANCELLED' => 'cancelled',
            'REFUNDED' => 'refunded'
        ];

        foreach ($expectedValues as $caseName => $expectedValue) {
            $enum = constant(OrderStatusEnum::class . '::' . $caseName);
            $this->assertEquals($expectedValue, $enum->value, "Incorrect value for {$caseName}");
        }
    }

    #[Test]
    public function get_available_statuses_returns_all_enum_values(): void
    {
        $statuses = OrderStatusEnum::getAvailableStatuses();
        
        $expectedStatuses = [
            'pending',
            'confirmed',
            'processing',
            'shipped',
            'delivered',
            'cancelled',
            'refunded'
        ];

        $this->assertIsArray($statuses);
        $this->assertCount(count($expectedStatuses), $statuses);
        
        foreach ($expectedStatuses as $expectedStatus) {
            $this->assertContains($expectedStatus, $statuses, "Missing status: {$expectedStatus}");
        }
    }

    #[Test]
    #[DataProvider('labelDataProvider')]
    public function get_label_returns_correct_human_readable_labels(OrderStatusEnum $enum, string $expectedLabel): void
    {
        $this->assertEquals($expectedLabel, $enum->getLabel());
    }

    public static function labelDataProvider(): array
    {
        return [
            [OrderStatusEnum::PENDING, 'Pending'],
            [OrderStatusEnum::CONFIRMED, 'Confirmed'],
            [OrderStatusEnum::PROCESSING, 'Processing'],
            [OrderStatusEnum::SHIPPED, 'Shipped'],
            [OrderStatusEnum::DELIVERED, 'Delivered'],
            [OrderStatusEnum::CANCELLED, 'Cancelled'],
            [OrderStatusEnum::REFUNDED, 'Refunded'],
        ];
    }

    #[Test]
    #[DataProvider('pendingTransitionDataProvider')]
    public function pending_status_can_transition_to_valid_statuses(OrderStatusEnum $targetStatus, bool $expectedResult): void
    {
        $pending = OrderStatusEnum::PENDING;
        $this->assertEquals($expectedResult, $pending->canTransitionTo($targetStatus));
    }

    public static function pendingTransitionDataProvider(): array
    {
        return [
            [OrderStatusEnum::PENDING, false],        // Cannot stay same
            [OrderStatusEnum::CONFIRMED, true],       // Can confirm
            [OrderStatusEnum::PROCESSING, false],     // Cannot skip to processing
            [OrderStatusEnum::SHIPPED, false],        // Cannot skip to shipped
            [OrderStatusEnum::DELIVERED, false],      // Cannot skip to delivered
            [OrderStatusEnum::CANCELLED, true],       // Can cancel
        ];
    }

    #[Test]
    #[DataProvider('confirmedTransitionDataProvider')]
    public function confirmed_status_can_transition_to_valid_statuses(OrderStatusEnum $targetStatus, bool $expectedResult): void
    {
        $confirmed = OrderStatusEnum::CONFIRMED;
        $this->assertEquals($expectedResult, $confirmed->canTransitionTo($targetStatus));
    }

    public static function confirmedTransitionDataProvider(): array
    {
        return [
            [OrderStatusEnum::PENDING, false],        // Cannot go back
            [OrderStatusEnum::CONFIRMED, false],      // Cannot stay same
            [OrderStatusEnum::PROCESSING, true],      // Can proceed to processing
            [OrderStatusEnum::SHIPPED, false],        // Cannot skip processing
            [OrderStatusEnum::DELIVERED, false],      // Cannot skip to delivered
            [OrderStatusEnum::CANCELLED, true],       // Can cancel
        ];
    }

    #[Test]
    #[DataProvider('processingTransitionDataProvider')]
    public function processing_status_can_transition_to_valid_statuses(OrderStatusEnum $targetStatus, bool $expectedResult): void
    {
        $processing = OrderStatusEnum::PROCESSING;
        $this->assertEquals($expectedResult, $processing->canTransitionTo($targetStatus));
    }

    public static function processingTransitionDataProvider(): array
    {
        return [
            [OrderStatusEnum::PENDING, false],        // Cannot go back
            [OrderStatusEnum::CONFIRMED, false],      // Cannot go back
            [OrderStatusEnum::PROCESSING, false],     // Cannot stay same
            [OrderStatusEnum::SHIPPED, true],         // Can proceed to shipped
            [OrderStatusEnum::DELIVERED, false],      // Cannot skip shipping
            [OrderStatusEnum::CANCELLED, true],       // Can cancel
        ];
    }

    #[Test]
    #[DataProvider('shippedTransitionDataProvider')]
    public function shipped_status_can_transition_to_valid_statuses(OrderStatusEnum $targetStatus, bool $expectedResult): void
    {
        $shipped = OrderStatusEnum::SHIPPED;
        $this->assertEquals($expectedResult, $shipped->canTransitionTo($targetStatus));
    }

    public static function shippedTransitionDataProvider(): array
    {
        return [
            [OrderStatusEnum::PENDING, false],        // Cannot go back
            [OrderStatusEnum::CONFIRMED, false],      // Cannot go back
            [OrderStatusEnum::PROCESSING, false],     // Cannot go back
            [OrderStatusEnum::SHIPPED, false],        // Cannot stay same
            [OrderStatusEnum::DELIVERED, true],       // Can deliver
            [OrderStatusEnum::CANCELLED, false],      // Cannot cancel when shipped
        ];
    }

    #[Test]
    #[DataProvider('deliveredTransitionDataProvider')]
    public function delivered_status_can_transition_to_refunded(OrderStatusEnum $targetStatus, bool $expectedResult): void
    {
        $delivered = OrderStatusEnum::DELIVERED;
        $this->assertEquals($expectedResult, $delivered->canTransitionTo($targetStatus));
    }

    public static function deliveredTransitionDataProvider(): array
    {
        return [
            [OrderStatusEnum::PENDING, false],        // Cannot go back
            [OrderStatusEnum::CONFIRMED, false],      // Cannot go back
            [OrderStatusEnum::PROCESSING, false],     // Cannot go back
            [OrderStatusEnum::SHIPPED, false],        // Cannot go back
            [OrderStatusEnum::DELIVERED, false],      // Cannot stay same
            [OrderStatusEnum::CANCELLED, false],      // Cannot go to cancelled
            [OrderStatusEnum::REFUNDED, true],        // Can be refunded
        ];
    }

    #[Test]
    #[DataProvider('cancelledTransitionDataProvider')]
    public function cancelled_status_can_transition_to_refunded(OrderStatusEnum $targetStatus, bool $expectedResult): void
    {
        $cancelled = OrderStatusEnum::CANCELLED;
        $this->assertEquals($expectedResult, $cancelled->canTransitionTo($targetStatus));
    }

    public static function cancelledTransitionDataProvider(): array
    {
        return [
            [OrderStatusEnum::PENDING, false],        // Cannot go back
            [OrderStatusEnum::CONFIRMED, false],      // Cannot go back
            [OrderStatusEnum::PROCESSING, false],     // Cannot go back
            [OrderStatusEnum::SHIPPED, false],        // Cannot go back
            [OrderStatusEnum::DELIVERED, false],      // Cannot go to delivered
            [OrderStatusEnum::CANCELLED, false],      // Cannot stay same
            [OrderStatusEnum::REFUNDED, true],        // Can be refunded
        ];
    }

    #[Test]
    #[DataProvider('refundedTransitionDataProvider')]
    public function refunded_status_cannot_transition_to_any_status(OrderStatusEnum $targetStatus, bool $expectedResult): void
    {
        $refunded = OrderStatusEnum::REFUNDED;
        $this->assertEquals($expectedResult, $refunded->canTransitionTo($targetStatus));
    }

    public static function refundedTransitionDataProvider(): array
    {
        return [
            [OrderStatusEnum::PENDING, false],        // Final state
            [OrderStatusEnum::CONFIRMED, false],      // Final state
            [OrderStatusEnum::PROCESSING, false],     // Final state
            [OrderStatusEnum::SHIPPED, false],        // Final state
            [OrderStatusEnum::DELIVERED, false],      // Final state
            [OrderStatusEnum::CANCELLED, false],      // Final state
            [OrderStatusEnum::REFUNDED, false],       // Final state
        ];
    }

    #[Test]
    public function valid_order_workflow_progression_works(): void
    {
        // Test complete successful order flow
        $pending = OrderStatusEnum::PENDING;
        $confirmed = OrderStatusEnum::CONFIRMED;
        $processing = OrderStatusEnum::PROCESSING;
        $shipped = OrderStatusEnum::SHIPPED;
        $delivered = OrderStatusEnum::DELIVERED;

        // Test the complete flow
        $this->assertTrue($pending->canTransitionTo($confirmed), 'Pending should transition to Confirmed');
        $this->assertTrue($confirmed->canTransitionTo($processing), 'Confirmed should transition to Processing');
        $this->assertTrue($processing->canTransitionTo($shipped), 'Processing should transition to Shipped');
        $this->assertTrue($shipped->canTransitionTo($delivered), 'Shipped should transition to Delivered');
    }

    #[Test]
    public function cancellation_workflow_works_correctly(): void
    {
        $cancelled = OrderStatusEnum::CANCELLED;

        // These statuses can be cancelled
        $cancellableStatuses = [
            OrderStatusEnum::PENDING,
            OrderStatusEnum::CONFIRMED,
            OrderStatusEnum::PROCESSING
        ];

        foreach ($cancellableStatuses as $status) {
            $this->assertTrue(
                $status->canTransitionTo($cancelled),
                "Status {$status->name} should be cancellable"
            );
        }

        // These statuses cannot be cancelled
        $nonCancellableStatuses = [
            OrderStatusEnum::SHIPPED,
            OrderStatusEnum::DELIVERED,
            OrderStatusEnum::CANCELLED
        ];

        foreach ($nonCancellableStatuses as $status) {
            $this->assertFalse(
                $status->canTransitionTo($cancelled),
                "Status {$status->name} should not be cancellable"
            );
        }
    }

    #[Test]
    public function enum_instances_can_be_created_from_values(): void
    {
        $pending = OrderStatusEnum::from('pending');
        $this->assertSame(OrderStatusEnum::PENDING, $pending);

        $delivered = OrderStatusEnum::from('delivered');
        $this->assertSame(OrderStatusEnum::DELIVERED, $delivered);
    }

    #[Test]
    public function try_from_returns_null_for_invalid_values(): void
    {
        $result = OrderStatusEnum::tryFrom('invalid_status');
        $this->assertNull($result);

        $result = OrderStatusEnum::tryFrom('');
        $this->assertNull($result);

        $result = OrderStatusEnum::tryFrom('unknown');
        $this->assertNull($result);
    }

    #[Test]
    public function enum_can_be_serialized_to_json(): void
    {
        $pending = OrderStatusEnum::PENDING;
        $json = json_encode($pending);
        
        $this->assertEquals('"pending"', $json);
    }

    #[Test]
    public function enum_values_are_unique(): void
    {
        $values = array_map(fn($case) => $case->value, OrderStatusEnum::cases());
        $uniqueValues = array_unique($values);
        
        $this->assertCount(count($values), $uniqueValues, 'Enum values must be unique');
    }

    #[Test]
    public function all_labels_are_non_empty_strings(): void
    {
        foreach (OrderStatusEnum::cases() as $case) {
            $label = $case->getLabel();
            $this->assertIsString($label, "Label for {$case->name} should be string");
            $this->assertNotEmpty($label, "Label for {$case->name} should not be empty");
        }
    }

    #[Test]
    public function transition_method_returns_boolean(): void
    {
        foreach (OrderStatusEnum::cases() as $fromCase) {
            foreach (OrderStatusEnum::cases() as $toCase) {
                $result = $fromCase->canTransitionTo($toCase);
                $this->assertIsBool(
                    $result,
                    "canTransitionTo from {$fromCase->name} to {$toCase->name} should return boolean"
                );
            }
        }
    }

    #[Test]
    public function no_status_can_transition_to_itself(): void
    {
        foreach (OrderStatusEnum::cases() as $case) {
            $this->assertFalse(
                $case->canTransitionTo($case),
                "Status {$case->name} should not transition to itself"
            );
        }
    }

    #[Test]
    public function final_states_cannot_transition_anywhere(): void
    {
        $finalStates = [OrderStatusEnum::REFUNDED];
        
        foreach ($finalStates as $finalState) {
            foreach (OrderStatusEnum::cases() as $targetState) {
                $this->assertFalse(
                    $finalState->canTransitionTo($targetState),
                    "Final state {$finalState->name} should not transition to {$targetState->name}"
                );
            }
        }
    }

    #[Test]
    public function backward_transitions_are_not_allowed(): void
    {
        $progressiveStatuses = [
            OrderStatusEnum::PENDING,
            OrderStatusEnum::CONFIRMED,
            OrderStatusEnum::PROCESSING,
            OrderStatusEnum::SHIPPED,
            OrderStatusEnum::DELIVERED
        ];

        for ($i = 1; $i < count($progressiveStatuses); $i++) {
            $currentStatus = $progressiveStatuses[$i];
            
            // Check that current status cannot go back to any previous status
            for ($j = 0; $j < $i; $j++) {
                $previousStatus = $progressiveStatuses[$j];
                $this->assertFalse(
                    $currentStatus->canTransitionTo($previousStatus),
                    "Status {$currentStatus->name} should not transition back to {$previousStatus->name}"
                );
            }
        }
    }

    #[Test]
    public function progressive_flow_allows_only_next_or_cancel(): void
    {
        // Test PENDING transitions
        $pending = OrderStatusEnum::PENDING;
        $this->assertTrue($pending->canTransitionTo(OrderStatusEnum::CONFIRMED));
        $this->assertTrue($pending->canTransitionTo(OrderStatusEnum::CANCELLED));
        $this->assertFalse($pending->canTransitionTo(OrderStatusEnum::PROCESSING));
        $this->assertFalse($pending->canTransitionTo(OrderStatusEnum::SHIPPED));
        $this->assertFalse($pending->canTransitionTo(OrderStatusEnum::DELIVERED));
        
        // Test CONFIRMED transitions
        $confirmed = OrderStatusEnum::CONFIRMED;
        $this->assertTrue($confirmed->canTransitionTo(OrderStatusEnum::PROCESSING));
        $this->assertTrue($confirmed->canTransitionTo(OrderStatusEnum::CANCELLED));
        $this->assertFalse($confirmed->canTransitionTo(OrderStatusEnum::PENDING));
        $this->assertFalse($confirmed->canTransitionTo(OrderStatusEnum::SHIPPED));
        $this->assertFalse($confirmed->canTransitionTo(OrderStatusEnum::DELIVERED));
        
        // Test PROCESSING transitions
        $processing = OrderStatusEnum::PROCESSING;
        $this->assertTrue($processing->canTransitionTo(OrderStatusEnum::SHIPPED));
        $this->assertTrue($processing->canTransitionTo(OrderStatusEnum::CANCELLED));
        $this->assertFalse($processing->canTransitionTo(OrderStatusEnum::PENDING));
        $this->assertFalse($processing->canTransitionTo(OrderStatusEnum::CONFIRMED));
        $this->assertFalse($processing->canTransitionTo(OrderStatusEnum::DELIVERED));
        
        // Test SHIPPED transitions
        $shipped = OrderStatusEnum::SHIPPED;
        $this->assertTrue($shipped->canTransitionTo(OrderStatusEnum::DELIVERED));
        $this->assertFalse($shipped->canTransitionTo(OrderStatusEnum::PENDING));
        $this->assertFalse($shipped->canTransitionTo(OrderStatusEnum::CONFIRMED));
        $this->assertFalse($shipped->canTransitionTo(OrderStatusEnum::PROCESSING));
        $this->assertFalse($shipped->canTransitionTo(OrderStatusEnum::CANCELLED)); // Cannot cancel shipped orders
    }

    #[Test]
    public function all_expected_methods_exist(): void
    {
        $reflection = new \ReflectionClass(OrderStatusEnum::class);
        
        $expectedMethods = [
            'getAvailableStatuses',
            'getLabel',
            'canTransitionTo'
        ];

        foreach ($expectedMethods as $methodName) {
            $this->assertTrue(
                $reflection->hasMethod($methodName),
                "Method {$methodName} should exist in OrderStatusEnum"
            );
        }
    }

    #[Test]
    public function static_method_returns_array(): void
    {
        $statuses = OrderStatusEnum::getAvailableStatuses();
        $this->assertIsArray($statuses, 'getAvailableStatuses should return an array');
        $this->assertNotEmpty($statuses, 'getAvailableStatuses should not return empty array');
    }

    #[Test]
    public function enum_case_comparison_works_correctly(): void
    {
        $pending1 = OrderStatusEnum::PENDING;
        $pending2 = OrderStatusEnum::from('pending');
        $confirmed = OrderStatusEnum::CONFIRMED;

        $this->assertTrue($pending1 === $pending2, 'Same enum instances should be identical');
        $this->assertFalse($pending1 === $confirmed, 'Different enum instances should not be identical');
    }
}