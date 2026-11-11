<?php
declare(strict_types=1);

namespace Imahmood\Money\ValueObjects;

use InvalidArgumentException;

final class Currency
{
    public function __construct(
        public readonly string $code,
        public readonly int $decimals,
        public readonly string $decimalPoint,
        public readonly string $thousandsSep,
        public readonly string $symbolLeft,
        public readonly string $symbolRight,
    ) {
        if (trim($this->code) === '') {
            throw new InvalidArgumentException('Currency code must not be empty.');
        }

        if ($this->decimals < 0) {
            throw new InvalidArgumentException('Currency decimals must be zero or greater.');
        }
    }

    public function equals(self $currency): bool
    {
        return strcasecmp($this->code, $currency->code) === 0;
    }
}
