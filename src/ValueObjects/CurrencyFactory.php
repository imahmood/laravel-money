<?php
declare(strict_types=1);

namespace Imahmood\Money\ValueObjects;

use InvalidArgumentException;

class CurrencyFactory
{
    /**
     * @throws \InvalidArgumentException
     */
    public static function createFromIsoCode(string $code): Currency
    {
        $config = config('money.currencies.'.$code);
        if ($config === null) {
            throw new InvalidArgumentException("Currency code '$code' is not supported.");
        }

        return new Currency(
            code: $code,
            decimals: (int) $config['decimals'],
            decimalPoint: $config['decimalPoint'],
            thousandsSep: $config['thousandsSep'],
            symbolLeft: $config['symbolLeft'],
            symbolRight: $config['symbolRight'],
        );
    }

    public static function defaultCurrency(): Currency
    {
        return static::createFromIsoCode(config('money.defaultCurrency'));
    }

    public static function localCurrency(): Currency
    {
        return static::createFromIsoCode(config('money.localCurrency'));
    }
}
