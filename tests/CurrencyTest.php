<?php
declare(strict_types=1);

namespace Imahmood\Money\Tests;

use Imahmood\Money\ValueObjects\Currency;
use InvalidArgumentException;

class CurrencyTest extends TestCase
{
    public function test_it_accepts_a_non_empty_currency_code_and_zero_decimals(): void
    {
        $currency = new Currency(
            code: 'USD',
            decimals: 0,
            decimalPoint: '.',
            thousandsSep: ',',
            symbolLeft: '$',
            symbolRight: '',
        );

        $this->assertSame('USD', $currency->code);
        $this->assertSame(0, $currency->decimals);
    }

    public function test_it_rejects_an_empty_currency_code(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Currency code must not be empty.');

        new Currency(
            code: '   ',
            decimals: 2,
            decimalPoint: '.',
            thousandsSep: ',',
            symbolLeft: '$',
            symbolRight: '',
        );
    }

    public function test_it_rejects_negative_currency_decimals(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Currency decimals must be zero or greater.');

        new Currency(
            code: 'USD',
            decimals: -1,
            decimalPoint: '.',
            thousandsSep: ',',
            symbolLeft: '$',
            symbolRight: '',
        );
    }
}
