<?php
declare(strict_types=1);

namespace Imahmood\Money\ValueObjects;

final class Currency
{
    public function __construct(
        public readonly string $code,
        public readonly int $decimals,
        public readonly string $decimalPoint,
        public readonly string $thousandsSep,
        public readonly string $symbolLeft,
        public readonly string $symbolRight,
    ) {}

    public function equals(self $currency): bool
    {
        return strcasecmp($this->code, $currency->code) === 0;
    }
}
