<?php
declare(strict_types=1);

namespace Imahmood\Money\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Imahmood\Money\Exceptions\CurrencyMismatchException;
use Imahmood\Money\ValueObjects\Currency;
use Imahmood\Money\ValueObjects\CurrencyFactory;
use Imahmood\Money\ValueObjects\Money;
use InvalidArgumentException;

/**
 * @implements \Illuminate\Contracts\Database\Eloquent\CastsAttributes<\Imahmood\Money\ValueObjects\Money, \Decimal\Decimal|string|int|\Imahmood\Money\ValueObjects\Money>
 */
class MoneyCast implements CastsAttributes
{
    public function __construct(
        private readonly ?string $currencyAttribute = null,
    ) {}

    /**
     * Cast the given value.
     *
     * @throws \InvalidArgumentException
     * @throws \Imahmood\Money\Exceptions\CurrencyNotSupportedException
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Money
    {
        if ($value === null) {
            return null;
        }

        return money($value, $this->resolveCurrency($model, $attributes));
    }

    /**
     * Prepare the given value for storage.
     *
     * @throws \InvalidArgumentException
     * @throws \Imahmood\Money\Exceptions\CurrencyMismatchException
     * @throws \Imahmood\Money\Exceptions\CurrencyNotSupportedException
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (! $value instanceof Money) {
            return $value === null ? null : (empty($value) ? '0' : (string) $value);
        }

        if ($this->currencyAttribute === null) {
            return $value->toString();
        }

        $currency = $this->resolveCurrency($model, $attributes);

        if (! $currency->equals($value->currency())) {
            throw CurrencyMismatchException::create($value->currency(), $currency);
        }

        return $value->toString();
    }

    /**
     * Determine if the given values are equal.
     *
     * @throws \InvalidArgumentException
     * @throws \Imahmood\Money\Exceptions\CurrencyNotSupportedException
     */
    public function compare(Model $model, string $key, mixed $firstValue, mixed $secondValue): bool
    {
        $currency = $this->resolveCurrency($model);

        return $this->toMoney($firstValue, $currency)
            ->equals($this->toMoney($secondValue, $currency));
    }

    /**
     * Resolve the currency used by this cast.
     *
     * @param  array<string, mixed>  $attributes
     *
     * @throws \Imahmood\Money\Exceptions\CurrencyNotSupportedException
     */
    private function resolveCurrency(Model $model, array $attributes = []): Currency
    {
        if ($this->currencyAttribute === null) {
            return CurrencyFactory::defaultCurrency();
        }

        $currencyCode = array_key_exists($this->currencyAttribute, $attributes)
            ? $attributes[$this->currencyAttribute]
            : $model->getAttribute($this->currencyAttribute);

        if ($currencyCode === null || trim((string) $currencyCode) === '') {
            throw new InvalidArgumentException(sprintf(
                'Currency attribute "%s" is required to cast money.',
                $this->currencyAttribute,
            ));
        }

        return CurrencyFactory::createFromIsoCode((string) $currencyCode);
    }

    private function toMoney(mixed $value, Currency $currency): Money
    {
        return $value instanceof Money ? $value : money($value, $currency);
    }
}
