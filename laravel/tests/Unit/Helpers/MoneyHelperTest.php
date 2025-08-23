<?php

namespace Tests\Unit\Helpers;

use App\Helpers\MoneyHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Base\UnitTestCase;

#[CoversClass(MoneyHelper::class)]
class MoneyHelperTest extends UnitTestCase
{
    #[Test]
    public function it_gets_amount_info_for_positive_value(): void
    {
        $result = MoneyHelper::getAmountInfo(12345, '₺');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('raw', $result);
        $this->assertArrayHasKey('formatted', $result);
        $this->assertArrayHasKey('formatted_minus', $result);
        $this->assertArrayHasKey('type', $result);
        
        $this->assertEquals(123.45, $result['raw']);
        $this->assertEquals('123.45 ₺', $result['formatted']);
        $this->assertEquals('123.45 ₺', $result['formatted_minus']);
        $this->assertEquals('positive', $result['type']);
    }

    #[Test]
    public function it_gets_amount_info_for_negative_value(): void
    {
        $result = MoneyHelper::getAmountInfo(-12345, '₺');
        
        $this->assertIsArray($result);
        $this->assertEquals(-123.45, $result['raw']);
        $this->assertEquals('-123.45 ₺', $result['formatted']);
        $this->assertEquals('-123.45 ₺', $result['formatted_minus']);
        $this->assertEquals('negative', $result['type']);
    }

    #[Test]
    public function it_gets_amount_info_for_zero_value(): void
    {
        $result = MoneyHelper::getAmountInfo(0, '₺');
        
        $this->assertIsArray($result);
        $this->assertEquals(0.0, $result['raw']);
        $this->assertEquals('0.00 ₺', $result['formatted']);
        $this->assertEquals('0.00 ₺', $result['formatted_minus']);
        $this->assertEquals('nil', $result['type']);
    }

    #[Test]
    public function it_gets_amount_info_with_default_currency(): void
    {
        $result = MoneyHelper::getAmountInfo(50000);
        
        $this->assertEquals(500.0, $result['raw']);
        $this->assertEquals('500.00 ₺', $result['formatted']);
        $this->assertEquals('positive', $result['type']);
    }

    #[Test]
    public function it_gets_amount_info_with_custom_currency(): void
    {
        $result = MoneyHelper::getAmountInfo(100000, '$');
        
        $this->assertEquals(1000.0, $result['raw']);
        $this->assertEquals('1,000.00 $', $result['formatted']);
        $this->assertEquals('positive', $result['type']);
    }

    #[Test]
    #[DataProvider('currencyDataProvider')]
    public function it_gets_amount_info_with_various_currencies(int $value, string $currency, string $expectedFormatted): void
    {
        $result = MoneyHelper::getAmountInfo($value, $currency);
        
        $this->assertEquals($expectedFormatted, $result['formatted']);
    }

    public static function currencyDataProvider(): array
    {
        return [
            'Turkish Lira' => [100000, '₺', '1,000.00 ₺'],
            'US Dollar' => [250000, '$', '2,500.00 $'],
            'Euro' => [350000, '€', '3,500.00 €'],
            'British Pound' => [125000, '£', '1,250.00 £'],
            'Japanese Yen' => [50000, '¥', '500.00 ¥'],
        ];
    }

    #[Test]
    public function it_formats_amount_with_default_currency(): void
    {
        $result = MoneyHelper::formatAmount(12345);
        
        $this->assertEquals('123.45 ₺', $result);
    }

    #[Test]
    public function it_formats_amount_with_custom_currency(): void
    {
        $result = MoneyHelper::formatAmount(250000, '$');
        
        $this->assertEquals('2,500.00 $', $result);
    }

    #[Test]
    public function it_formats_zero_amount(): void
    {
        $result = MoneyHelper::formatAmount(0, '₺');
        
        $this->assertEquals('0.00 ₺', $result);
    }

    #[Test]
    public function it_formats_negative_amount(): void
    {
        $result = MoneyHelper::formatAmount(-12345, '₺');
        
        $this->assertEquals('-123.45 ₺', $result);
    }

    #[Test]
    #[DataProvider('formatAmountDataProvider')]
    public function it_formats_various_amounts(int $value, string $currency, string $expected): void
    {
        $result = MoneyHelper::formatAmount($value, $currency);
        
        $this->assertEquals($expected, $result);
    }

    public static function formatAmountDataProvider(): array
    {
        return [
            'Small amount' => [1, '₺', '0.01 ₺'],
            'Medium amount' => [123456, '₺', '1,234.56 ₺'],
            'Large amount' => [1234567890, '₺', '12,345,678.90 ₺'],
            'Zero amount' => [0, '$', '0.00 $'],
            'Negative small' => [-1, '€', '-0.01 €'],
            'Negative large' => [-1234567890, '£', '-12,345,678.90 £'],
        ];
    }

    #[Test]
    public function it_converts_float_to_minor_units(): void
    {
        $result = MoneyHelper::convertToMinorUnits(123.45);
        
        $this->assertEquals(12345, $result);
        $this->assertIsInt($result);
    }

    #[Test]
    public function it_converts_zero_float_to_minor_units(): void
    {
        $result = MoneyHelper::convertToMinorUnits(0.0);
        
        $this->assertEquals(0, $result);
        $this->assertIsInt($result);
    }

    #[Test]
    public function it_converts_negative_float_to_minor_units(): void
    {
        $result = MoneyHelper::convertToMinorUnits(-123.45);
        
        $this->assertEquals(-12345, $result);
        $this->assertIsInt($result);
    }

    #[Test]
    public function it_handles_rounding_when_converting_to_minor_units(): void
    {
        // Test values that require rounding
        $this->assertEquals(1234, MoneyHelper::convertToMinorUnits(12.335)); // Should round to 12.34
        $this->assertEquals(1235, MoneyHelper::convertToMinorUnits(12.345)); // Should round to 12.35
        $this->assertEquals(1235, MoneyHelper::convertToMinorUnits(12.346)); // Should round to 12.35
    }

    #[Test]
    #[DataProvider('minorUnitsDataProvider')]
    public function it_converts_various_floats_to_minor_units(float $amount, int $expected): void
    {
        $result = MoneyHelper::convertToMinorUnits($amount);
        
        $this->assertEquals($expected, $result);
    }

    public static function minorUnitsDataProvider(): array
    {
        return [
            'Small decimal' => [0.01, 1],
            'Regular amount' => [99.99, 9999],
            'Large amount' => [12345.67, 1234567],
            'Zero' => [0.00, 0],
            'Negative small' => [-0.01, -1],
            'Negative regular' => [-99.99, -9999],
            'Three decimals' => [12.345, 1235], // Should round
            'Many decimals' => [12.3456789, 1235], // Should round
        ];
    }

    #[Test]
    public function it_converts_minor_units_to_float(): void
    {
        $result = MoneyHelper::convertFromMinorUnits(12345);
        
        $this->assertEquals(123.45, $result);
        $this->assertIsFloat($result);
    }

    #[Test]
    public function it_converts_zero_minor_units_to_float(): void
    {
        $result = MoneyHelper::convertFromMinorUnits(0);
        
        $this->assertEquals(0.0, $result);
        $this->assertIsFloat($result);
    }

    #[Test]
    public function it_converts_negative_minor_units_to_float(): void
    {
        $result = MoneyHelper::convertFromMinorUnits(-12345);
        
        $this->assertEquals(-123.45, $result);
        $this->assertIsFloat($result);
    }

    #[Test]
    #[DataProvider('fromMinorUnitsDataProvider')]
    public function it_converts_various_minor_units_to_floats(int $value, float $expected): void
    {
        $result = MoneyHelper::convertFromMinorUnits($value);
        
        $this->assertEquals($expected, $result);
    }

    public static function fromMinorUnitsDataProvider(): array
    {
        return [
            'Single unit' => [1, 0.01],
            'Regular amount' => [9999, 99.99],
            'Large amount' => [1234567, 12345.67],
            'Zero' => [0, 0.0],
            'Negative single' => [-1, -0.01],
            'Negative regular' => [-9999, -99.99],
            'Even hundreds' => [10000, 100.0],
            'Odd amount' => [12301, 123.01],
        ];
    }

    #[Test]
    public function it_handles_round_trip_conversion_accurately(): void
    {
        $originalAmounts = [0.0, 1.23, 99.99, 123.45, 1000.0, -50.75];
        
        foreach ($originalAmounts as $originalAmount) {
            $minorUnits = MoneyHelper::convertToMinorUnits($originalAmount);
            $convertedBack = MoneyHelper::convertFromMinorUnits($minorUnits);
            
            $this->assertEquals(
                $originalAmount, 
                $convertedBack,
                "Round trip conversion failed for amount: {$originalAmount}"
            );
        }
    }

    #[Test]
    public function it_handles_precision_edge_cases(): void
    {
        // Test edge cases that might cause precision issues
        $edgeCases = [
            [0.1 + 0.2, 0.30], // Classic floating point precision issue
            [0.29 + 0.01, 0.30],
            [1.005, 1.01], // Rounding edge case (should round to 1.01)
            [2.995, 3.00], // Should round to 3.00 (fixed expected value)
        ];
        
        foreach ($edgeCases as [$input, $expectedAfterRoundTrip]) {
            $minorUnits = MoneyHelper::convertToMinorUnits($input);
            $result = MoneyHelper::convertFromMinorUnits($minorUnits);
            
            // Allow for small floating point differences
            $this->assertEqualsWithDelta(
                $expectedAfterRoundTrip,
                $result,
                0.001,
                "Precision edge case failed for input: {$input}"
            );
        }
    }

    #[Test]
    public function it_maintains_consistency_between_format_methods(): void
    {
        $values = [0, 1, 12345, 99999, -12345];
        
        foreach ($values as $value) {
            $amountInfo = MoneyHelper::getAmountInfo($value, '₺');
            $directFormat = MoneyHelper::formatAmount($value, '₺');
            
            $this->assertEquals(
                $directFormat,
                $amountInfo['formatted'],
                "Format consistency failed for value: {$value}"
            );
        }
    }

    #[Test]
    public function it_handles_very_large_amounts(): void
    {
        $largeValue = 999999999999; // 9,999,999,999.99
        
        $result = MoneyHelper::getAmountInfo($largeValue, '₺');
        
        $this->assertEquals(9999999999.99, $result['raw']);
        $this->assertEquals('9,999,999,999.99 ₺', $result['formatted']);
        $this->assertEquals('positive', $result['type']);
    }

    #[Test]
    public function it_handles_very_small_amounts(): void
    {
        $smallValue = 1; // 0.01
        
        $result = MoneyHelper::getAmountInfo($smallValue, '₺');
        
        $this->assertEquals(0.01, $result['raw']);
        $this->assertEquals('0.01 ₺', $result['formatted']);
        $this->assertEquals('positive', $result['type']);
    }

    #[Test]
    public function it_handles_empty_currency_symbol(): void
    {
        $result = MoneyHelper::formatAmount(12345, '');
        
        $this->assertEquals('123.45 ', $result);
    }

    #[Test]
    public function it_handles_special_currency_symbols(): void
    {
        $specialSymbols = ['¢', '₹', '₽', '₩', '₪', '₫'];
        
        foreach ($specialSymbols as $symbol) {
            $result = MoneyHelper::formatAmount(12345, $symbol);
            
            $this->assertEquals("123.45 {$symbol}", $result);
            $this->assertStringContainsString($symbol, $result);
        }
    }
}