<?php
declare(strict_types=1);

namespace Imahmood\Money\ValueObjects;

use Decimal\Decimal;
use Imahmood\Money\Exceptions\CurrencyMismatchException;
use InvalidArgumentException;
use JsonSerializable;

final class Money implements JsonSerializable
{
    private readonly Decimal $value;

    private readonly Currency $currency;

    public function __construct(Decimal|string|int $value, Currency $currency)
    {
        $this->value = decimal($value);
        $this->currency = $currency;
    }

    public function isZero(): bool
    {
        return $this->value->isZero();
    }

    public function isNegative(): bool
    {
        return $this->value->isNegative();
    }

    public function equals(Money|Decimal|int $value): bool
    {
        try {
            $this->validateValue($value);
        } catch (CurrencyMismatchException) {
            return false;
        }

        return $this->value->equals((string) $value);
    }

    /**
     * @throws \Imahmood\Money\Exceptions\CurrencyMismatchException
     */
    public function add(Money|Decimal|int $value): self
    {
        $this->validateValue($value);

        return new self(
            $this->value->add((string) $value),
            $this->currency,
        );
    }

    /**
     * @throws \Imahmood\Money\Exceptions\CurrencyMismatchException
     */
    public function sub(Money|Decimal|int $value): self
    {
        $this->validateValue($value);

        return new self(
            $this->value->sub((string) $value),
            $this->currency,
        );
    }

    /**
     * @throws \Imahmood\Money\Exceptions\CurrencyMismatchException
     */
    public function div(Money|Decimal|int $value): self
    {
        $this->validateValue($value);

        return new self(
            $this->value->div((string) $value),
            $this->currency,
        );
    }

    /**
     * @throws \Imahmood\Money\Exceptions\CurrencyMismatchException
     */
    public function mul(Money|Decimal|int $value): self
    {
        $this->validateValue($value);

        return new self(
            $this->value->mul((string) $value),
            $this->currency,
        );
    }

    /**
     * @throws \Imahmood\Money\Exceptions\CurrencyMismatchException
     */
    private function validateValue(Money|Decimal|int $value): void
    {
        if ($value instanceof self && ! $this->currency->equals($value->currency)) {
            throw new CurrencyMismatchException(sprintf(
                'Cannot perform operation between currencies "%s" and "%s".',
                $this->currency->code,
                $value->currency->code
            ));
        }
    }

    public function convert(Currency $currency): self
    {
        if ($currency->code === $this->currency->code) {
            return new self($this->value, $this->currency);
        }

        $exchangeRate = config(sprintf('money.exchange.%s.%s', $this->currency->code, $currency->code), 1);

        if ($exchangeRate === null) {
            throw new InvalidArgumentException(sprintf(
                'Exchange rate from %s to %s not found.',
                $this->currency->code,
                $currency->code,
            ));
        }

        return new self(
            $this->value->mul($exchangeRate),
            $currency,
        );
    }

    public function format(): string
    {
        return number_format(
            $this->value->toInt(),
            $this->currency->decimals,
            $this->currency->decimalPoint,
            $this->currency->thousandsSep,
        );
    }

    public function formatWithSymbol(): string
    {
        $left = $this->currency->symbolLeft ? $this->currency->symbolLeft.' ' : '';
        $right = $this->currency->symbolRight ? ' '.$this->currency->symbolRight : '';

        return $left.$this->format().$right;
    }

    public function currency(): Currency
    {
        return $this->currency;
    }

    public function toDecimal(): Decimal
    {
        return $this->value;
    }

    public function toInt(): int
    {
        return $this->value->toInt();
    }

    public function toString(): string
    {
        return $this->value->toString();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function jsonSerialize(): string
    {
        return $this->toString();
    }
}
