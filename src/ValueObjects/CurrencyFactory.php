<?php
declare(strict_types=1);

namespace Imahmood\Money\ValueObjects;

use Imahmood\Money\Exceptions\CurrencyNotSupportedException;

class CurrencyFactory
{
    /**
     * @throws \Imahmood\Money\Exceptions\CurrencyNotSupportedException
     */
    public static function createFromIsoCode(string $code): Currency
    {
        /**
         * @var array{decimals: int, decimalPoint: string, thousandsSep: string, symbolLeft: string, symbolRight: string}|null $config
         */
        $config = config('money.currencies.'.$code);
        if ($config === null) {
            throw new CurrencyNotSupportedException("Currency code '{$code}' is not supported.");
        }

        return new Currency(
            code: strtoupper($code),
            decimals: (int) $config['decimals'],
            decimalPoint: $config['decimalPoint'],
            thousandsSep: $config['thousandsSep'],
            symbolLeft: $config['symbolLeft'],
            symbolRight: $config['symbolRight'],
        );
    }

    /**
     * @throws \Imahmood\Money\Exceptions\CurrencyNotSupportedException
     */
    public static function defaultCurrency(): Currency
    {
        return static::createFromIsoCode((string) config('money.defaultCurrency'));
    }

    /**
     * @throws \Imahmood\Money\Exceptions\CurrencyNotSupportedException
     */
    public static function localCurrency(): Currency
    {
        return static::createFromIsoCode((string) config('money.localCurrency'));
    }
}
