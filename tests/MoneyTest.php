<?php
declare(strict_types=1);

namespace Imahmood\Money\Tests;

use Decimal\Decimal;
use Imahmood\Money\Exceptions\CurrencyMismatchException;
use Imahmood\Money\ValueObjects\Currency;
use Imahmood\Money\ValueObjects\Money;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

class MoneyTest extends TestCase
{
    public function test_create_money_and_expose_its_value_and_currency(): void
    {
        $currency = $this->currency();
        $money = new Money(100, $currency);
        $decimalMoney = new Money(new Decimal('100'), $currency);
        $stringMoney = new Money('100', $currency);

        $this->assertSame($currency, $money->currency());
        $this->assertInstanceOf(Decimal::class, $money->toDecimal());
        $this->assertSame(100, $money->toInt());
        $this->assertSame('100', $money->toString());
        $this->assertSame('100', (string) $money);
        $this->assertSame('"100"', json_encode($money, JSON_THROW_ON_ERROR));
        $this->assertSame('100', $decimalMoney->toString());
        $this->assertSame('100', $stringMoney->toString());
    }

    public function test_it_detects_zero_values(): void
    {
        $currency = $this->currency();

        $this->assertTrue((new Money(0, $currency))->isZero());
        $this->assertFalse((new Money(1, $currency))->isZero());
    }

    public function test_it_detects_negative_values(): void
    {
        $currency = $this->currency();

        $this->assertTrue((new Money(-1, $currency))->isNegative());
        $this->assertFalse((new Money(0, $currency))->isNegative());
        $this->assertFalse((new Money(1, $currency))->isNegative());
    }

    public function test_it_compares_values_with_ints_decimals_and_money(): void
    {
        $currency = $this->currency();
        $money = new Money(100, $currency);

        $this->assertTrue($money->equals(100));
        $this->assertTrue($money->equals(new Decimal('100')));
        $this->assertTrue($money->equals(new Money(100, $currency)));
        $this->assertFalse($money->equals(99));
        $this->assertFalse($money->equals(new Money(100, $this->currency('IRT', 'تومان'))));
    }

    public function test_it_adds_values_without_mutating_the_original(): void
    {
        $currency = $this->currency();
        $money = new Money(100, $currency);

        $result = $money->add(25);

        $this->assertSame('125', $result->toString());
        $this->assertSame($currency, $result->currency());
        $this->assertSame('100', $money->toString());
        $this->assertSame('125', $money->add(new Decimal('25'))->toString());
    }

    public function test_it_subtracts_values(): void
    {
        $currency = $this->currency();

        $this->assertSame('75', (new Money(100, $currency))->sub(25)->toString());
    }

    public function test_it_multiplies_values(): void
    {
        $currency = $this->currency();

        $this->assertSame('300', (new Money(100, $currency))->mul(3)->toString());
    }

    public function test_it_divides_values(): void
    {
        $currency = $this->currency();

        $this->assertSame('25', (new Money(100, $currency))->div(4)->toString());
    }

    public function test_it_accepts_money_operands_with_the_same_currency(): void
    {
        $currency = $this->currency();
        $money = new Money(100, $currency);
        $operand = new Money(25, $currency);

        $this->assertSame('125', $money->add($operand)->toString());
        $this->assertSame('75', $money->sub($operand)->toString());
        $this->assertSame('2500', $money->mul($operand)->toString());
        $this->assertSame('4', $money->div($operand)->toString());
    }

    #[DataProvider('currencyMismatchOperations')]
    public function test_it_rejects_operations_between_different_currencies(string $operation): void
    {
        $irr = $this->currency();
        $irt = $this->currency('IRT', 'تومان');
        $money = new Money(100, $irr);
        $operand = new Money(25, $irt);

        $this->expectException(CurrencyMismatchException::class);
        $this->expectExceptionMessageIs('Cannot perform operation between currencies "IRR" and "IRT".');

        $money->{$operation}($operand);
    }

    /**
     * @return list<array{string}>
     */
    public static function currencyMismatchOperations(): array
    {
        return [['add'], ['sub'], ['mul'], ['div']];
    }

    public function test_it_converts_between_configured_currencies(): void
    {
        $irr = $this->currency();
        $irt = $this->currency('IRT', 'تومان');
        config(['money.exchange.IRR.IRT' => '0.1']);
        $money = new Money(100, $irr);

        $converted = $money->convert($irt);

        $this->assertSame('10', $converted->toString());
        $this->assertSame($irt, $converted->currency());
        $this->assertSame('100', $money->toString());
    }

    public function test_money_helpers_accept_string_amounts(): void
    {
        config([
            'money.exchange.IRR.IRT' => '0.1',
            'money.exchange.IRT.IRR' => '10',
        ]);

        $money = money('100', $this->currency());
        $fromLocal = from_local_currency('100');
        $toLocal = to_local_currency('100');

        $this->assertSame('100', $money->toString());
        $this->assertSame('1000', $fromLocal->toString());
        $this->assertSame('10', $toLocal->toString());
        $this->assertSame('IRR', $fromLocal->currency()->code);
        $this->assertSame('IRT', $toLocal->currency()->code);
    }

    public function test_conversion_to_the_same_currency_returns_an_equivalent_new_instance(): void
    {
        $currency = $this->currency();
        $money = new Money(100, $currency);
        $converted = $money->convert($currency);

        $this->assertNotSame($money, $converted);
        $this->assertTrue($converted->equals($money));
        $this->assertSame($currency, $converted->currency());
    }

    public function test_it_throws_when_an_exchange_rate_is_explicitly_missing(): void
    {
        $currency = $this->currency();
        $target = new Currency(
            code: 'USD',
            decimals: 2,
            decimalPoint: '.',
            thousandsSep: ',',
            symbolLeft: '$',
            symbolRight: '',
        );
        config(['money.exchange.IRR.USD' => null]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Exchange rate from IRR to USD not found.');

        (new Money(100, $currency))->convert($target);
    }

    public function test_it_formats_values_with_currency_separators_and_symbols(): void
    {
        $money = new Money(1234567, $this->currency());

        $this->assertSame('1٬234٬567', $money->format());
        $this->assertSame('1٬234٬567 ریال', $money->formatWithSymbol());
    }

    private function currency(string $code = 'IRR', string $symbolRight = 'ریال'): Currency
    {
        return new Currency(
            code: $code,
            decimals: 0,
            decimalPoint: '٫',
            thousandsSep: '٬',
            symbolLeft: '',
            symbolRight: $symbolRight,
        );
    }
}
