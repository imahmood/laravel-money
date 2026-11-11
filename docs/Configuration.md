# Configuration

The package configuration is stored in `config/money.php`. Publish the file first if it does not exist in your application:

```bash
php artisan vendor:publish --provider="Imahmood\Money\MoneyServiceProvider"
```

## Default and local currencies

```php
'defaultCurrency' => 'IRR',
'localCurrency' => 'IRT',
```

`defaultCurrency` is used when creating money without explicitly passing a currency. `localCurrency` is used by the `from_local_currency()` and `to_local_currency()` helpers.

Both values must refer to currency codes defined in the `currencies` configuration.

## Define currencies

Each currency entry contains its code, formatting precision, separators, and optional symbols:

```php
'currencies' => [
    'USD' => [
        'decimals' => 2,
        'decimalPoint' => '.',
        'thousandsSep' => ',',
        'symbolLeft' => '$',
        'symbolRight' => '',
    ],
],
```

- `decimals`: Number of decimal places displayed by `format()`.
- `decimalPoint`: Decimal separator used for formatted output.
- `thousandsSep`: Thousands separator used for formatted output.
- `symbolLeft`: Optional symbol displayed before the formatted amount.
- `symbolRight`: Optional symbol displayed after the formatted amount.

Currency codes are used as configuration keys and should be kept consistent throughout the application.

## Configure exchange rates

Exchange rates are defined from a source currency to a target currency:

```php
'exchange' => [
    'IRR' => [
        'IRT' => 0.1,
    ],
    'IRT' => [
        'IRR' => 10,
    ],
],
```

For example, converting `100 IRR` to `IRT` multiplies the amount by the configured `IRR` to `IRT` rate.

Add a rate for every conversion direction your application needs. When a rate is absent, conversion currently falls back to `1`; an explicitly configured `null` rate throws an `InvalidArgumentException`. Exchange rates are read from configuration at runtime, so remember to clear or rebuild the configuration cache after changing them in a cached production environment.
