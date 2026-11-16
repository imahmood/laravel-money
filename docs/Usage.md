# Usage

The package exposes the `money()` helper for creating money values. If no currency is provided, the configured `defaultCurrency` is used. Use the `currency()` helper to create a configured currency from its ISO code; codes are trimmed and normalized to uppercase. The examples below assume USD and EUR have been added to `config/money.php`.

```php
$amount = money(1500);
$amount->currency()->code; // configured default currency

$usd = currency(' usd ');
$amount = money('19.99', 'USD');
$sameAmount = money('19.99', $usd);
```

Amounts may be integers, numeric strings, or `Decimal\Decimal` instances. Decimal arithmetic is used internally for precise calculations.

## Arithmetic

Money values are immutable: each arithmetic method returns a new `Money` instance and leaves the original unchanged.

```php
$amount = money(100, 'USD');

$amount->add(25)->toString(); // 125
$amount->sub(10)->toString(); // 90
$amount->mul(3)->toString();  // 300
$amount->div(4)->toString();  // 25
```

The operand can also be another `Money` instance or a `Decimal\Decimal` instance:

```php
$amount->add(money(25, 'USD'));
```

Operations between two money values require matching currencies. A `CurrencyMismatchException` is thrown when their currencies differ.

## Compare and inspect values

```php
$amount = money(100, 'USD');

$amount->equals(100);       // true
$amount->equals(money(100, 'USD')); // true
$amount->isZero();          // false
$amount->isNegative();      // false
$amount->isPositive();      // true
$amount->isSameCurrency(money(50, 'USD')); // true

$amount->greaterThan(50);           // true
$amount->lessThan(200);             // true
$amount->greaterThanOrEqual(100);   // true
$amount->lessThanOrEqual(100);      // true
```

Comparison methods accept integers, `Decimal\Decimal` instances, or `Money` values with the same currency. Comparisons between different currencies throw a `CurrencyMismatchException`.

Use `zero()` to create a zero-valued `Money` instance. It uses the configured default currency unless a currency is provided:

```php
use Imahmood\Money\ValueObjects\Money;

$zero = Money::zero();
$eurZero = Money::zero(currency('EUR'));
```

Use these methods to access the value and currency:

```php
$amount->toDecimal();             // Decimal\Decimal
$amount->toInt();                 // 100
$amount->toString();              // "100"
$amount->currency()->code;        // "USD"
(string) $amount;                 // "100"
json_encode($amount);             // "\"100\""
```

## Convert currencies

Pass a configured target currency to `convert()`:

```php
$usd = money(1000, 'USD');
$eur = $usd->convert(currency('EUR'));

$eur->toString(); // depends on the configured USD -> EUR exchange rate
$eur->currency()->code; // EUR
```

The conversion uses the rate configured for the source and target currency. If an exchange rate is explicitly configured as `null`, an `InvalidArgumentException` is thrown.

The local-currency helpers provide shortcuts for the configured currencies:

```php
$default = from_local_currency(100); // local currency -> default currency
$local = to_local_currency(100);     // default currency -> local currency
```

## Format amounts

`format()` uses the target currency's configured separators and decimal precision. `formatWithSymbol()` also includes its configured symbols:

```php
$amount = money(1234567, 'USD');

$amount->format();           // 1,234,567
$amount->formatWithSymbol(); // $ 1,234,567
```

## Cast money values in Eloquent

Use `MoneyCast` to retrieve a database amount as a `Money` object and store it as a numeric string:

```php
use Imahmood\Money\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $casts = [
        'price' => MoneyCast::class,
    ];
}
```

When no currency attribute is configured, the cast uses the configured default currency when reading a stored value. For models containing multiple currencies, pass the name of the model attribute containing the currency code:

```php
class Product extends Model
{
    protected $casts = [
        'price' => MoneyCast::class.':currency_code',
    ];
}
```

The cast reads the currency from `currency_code` for each model row and throws when the configured attribute is missing or contains an unsupported code. It accepts a `Money` object, a numeric value, or `null` when writing. A `Money` object must use the same currency as the configured model attribute.

## Define and use a custom currency

Add the currency to `config/money.php`, then create it through the `currency()` helper:

```php
$usd = currency('USD');
$eur = currency('EUR');
$amount = money('19.99', $usd);
```

Attempting to create a currency code that is not configured throws a `CurrencyNotSupportedException`.
