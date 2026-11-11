<?php
declare(strict_types=1);

namespace Imahmood\Money\Tests;

use Decimal\Decimal;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

class FunctionsTest extends TestCase
{
    #[DataProvider('decimalValues')]
    public function test_it_creates_decimal_values(mixed $value, string $expected): void
    {
        $result = decimal($value);

        $this->assertInstanceOf(Decimal::class, $result);
        $this->assertSame($expected, $result->toString());
    }

    /**
     * @return list<array{mixed, string}>
     */
    public static function decimalValues(): array
    {
        return [
            [null, '0'],
            [0, '0'],
            ['100', '100'],
            ['100.00', '100.00'],
            ['-10.0', '-10.0'],
        ];
    }

    public function test_it_preserves_fractional_values(): void
    {
        $this->assertSame('10.50', decimal('10.50')->toString());
    }

    public function test_it_preserves_a_decimal_result_from_arithmetic(): void
    {
        $value = decimal('100')->div(10);

        $this->assertSame($value, decimal($value));
        $this->assertSame(0, $value->compareTo('10'));
    }

    public function test_it_returns_an_existing_fractional_decimal_instance(): void
    {
        $value = decimal('10.50');

        $this->assertSame($value, decimal($value));
    }

    public function test_it_rejects_values_that_cannot_be_converted(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExactExceptionMessage('Cannot convert value of type `array` to decimal');

        decimal([]);
    }
}
