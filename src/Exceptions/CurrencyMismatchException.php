<?php
declare(strict_types=1);

namespace Imahmood\Money\Exceptions;

use Exception;
use Imahmood\Money\ValueObjects\Currency;

class CurrencyMismatchException extends Exception
{
    public static function create(Currency $firstCurrency, Currency $secondCurrency): self
    {
        return new self(sprintf(
            'Cannot perform operation between currencies "%s" and "%s".',
            $firstCurrency->code,
            $secondCurrency->code,
        ));
    }
}
