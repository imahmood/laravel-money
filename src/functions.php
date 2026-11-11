<?php
declare(strict_types=1);

use Decimal\Decimal;
use Imahmood\Money\ValueObjects\Currency;
use Imahmood\Money\ValueObjects\CurrencyFactory;
use Imahmood\Money\ValueObjects\Money;

if (! function_exists('decimal')) {
    /**
     * @throws \InvalidArgumentException
     */
    function decimal(mixed $value, int $precision = Decimal::DEFAULT_PRECISION): Decimal
    {
        if ($value === null) {
            $value = 0;
        }

        if (is_numeric($value)) {
            $value = method_exists(Decimal::class, 'valueOf')
                ? Decimal::valueOf((string) $value, $precision)
                : new Decimal((string) $value, $precision);
        }

        if (! $value instanceof Decimal) {
            throw new InvalidArgumentException(
                sprintf('Cannot convert value of type `%s` to decimal', get_debug_type($value))
            );
        }

        return $value;
    }
}

if (! function_exists('money')) {
    /**
     * @throws \Imahmood\Money\Exceptions\CurrencyNotSupportedException
     */
    function money(Decimal|string|int $amount, ?Currency $currency = null): Money
    {
        $currency ??= CurrencyFactory::defaultCurrency();

        return new Money($amount, $currency);
    }
}

if (! function_exists('from_local_currency')) {
    /**
     * @throws \Imahmood\Money\Exceptions\CurrencyNotSupportedException
     */
    function from_local_currency(Decimal|string|int $amount): Money
    {
        return money($amount, CurrencyFactory::localCurrency())
            ->convert(CurrencyFactory::defaultCurrency());
    }
}

if (! function_exists('to_local_currency')) {
    /**
     * @throws \Imahmood\Money\Exceptions\CurrencyNotSupportedException
     */
    function to_local_currency(Decimal|string|int $amount): Money
    {
        return money($amount)->convert(CurrencyFactory::localCurrency());
    }
}
