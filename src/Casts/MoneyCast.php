<?php
declare(strict_types=1);

namespace Imahmood\Money\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Imahmood\Money\ValueObjects\Money;

class MoneyCast implements CastsAttributes
{
    /**
     * Cast the given value.
     */
    public function get(Model $model, string $key, $value, array $attributes): ?Money
    {
        if ($value === null) {
            return null;
        }

        return money($value);
    }

    /**
     * Prepare the given value for storage.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value instanceof Money) {
            return $value->toString();
        }

        if ($value === null) {
            return null;
        }

        return empty($value) ? '0' : (string) $value;
    }

    /**
     * Determine if the given values are equal.
     */
    public function compare(Model $model, string $key, mixed $firstValue, mixed $secondValue): bool
    {
        return money($firstValue)->equals($secondValue);
    }
}
