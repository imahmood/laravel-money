<?php
declare(strict_types=1);

namespace Imahmood\Money\Tests;

use Illuminate\Database\Eloquent\Model;
use Imahmood\Money\Casts\MoneyCast;
use Imahmood\Money\Exceptions\CurrencyMismatchException;
use Imahmood\Money\Exceptions\CurrencyNotSupportedException;
use Imahmood\Money\ValueObjects\CurrencyFactory;
use Imahmood\Money\ValueObjects\Money;
use InvalidArgumentException;

class MoneyCastTest extends TestCase
{
    public function test_it_uses_the_default_currency_without_a_currency_attribute(): void
    {
        $model = new DefaultMoneyCastModel;

        $model->setRawAttributes(['amount' => '100']);

        $this->assertSame('IRR', $model->amount->currency()->code);
    }

    public function test_it_uses_the_model_currency_attribute_when_configured(): void
    {
        $model = new MultiCurrencyModel;

        $model->setRawAttributes([
            'amount' => '100',
            'currency_code' => 'IRT',
        ]);

        $this->assertSame('IRT', $model->amount->currency()->code);
    }

    public function test_multiple_money_attributes_can_use_different_currencies(): void
    {
        $model = new MultiCurrencyModel;

        $model->setRawAttributes([
            'price' => '100',
            'price_currency' => 'IRR',
            'cost' => '100',
            'cost_currency' => 'IRT',
        ]);

        $this->assertSame('IRR', $model->price->currency()->code);
        $this->assertSame('IRT', $model->cost->currency()->code);
    }

    public function test_it_rejects_a_missing_currency_attribute(): void
    {
        $model = new MultiCurrencyModel;

        $model->setRawAttributes(['amount' => '100']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Currency attribute "currency_code" is required to cast money.');

        $model->getAttribute('amount');
    }

    public function test_it_rejects_an_unsupported_currency_attribute(): void
    {
        $model = new MultiCurrencyModel;

        $model->setRawAttributes([
            'amount' => '100',
            'currency_code' => 'USD',
        ]);

        $this->expectException(CurrencyNotSupportedException::class);

        $model->getAttribute('amount');
    }

    public function test_it_serializes_a_money_value_with_the_model_currency(): void
    {
        $model = new MultiCurrencyModel;

        $model->currency_code = 'IRT';
        $model->amount = money('100', CurrencyFactory::createFromIsoCode('IRT'));

        $this->assertSame('100', $model->getAttributes()['amount']);
    }

    public function test_it_rejects_a_money_value_with_a_different_currency(): void
    {
        $model = new MultiCurrencyModel;

        $model->currency_code = 'IRT';

        $this->expectException(CurrencyMismatchException::class);

        $model->amount = money('100');
    }

    public function test_it_compares_values_using_the_model_currency(): void
    {
        $model = new MultiCurrencyModel;
        $model->setRawAttributes(['currency_code' => 'IRT']);

        $cast = new MoneyCast('currency_code');

        $this->assertTrue($cast->compare($model, 'amount', '100', '100'));
        $this->assertFalse($cast->compare($model, 'amount', '100', '101'));
    }
}

/**
 * @property Money|null $amount
 */
class DefaultMoneyCastModel extends Model
{
    protected $casts = [
        'amount' => MoneyCast::class,
    ];
}

/**
 * @property string|null $currency_code
 * @property Money|null $amount
 * @property Money|null $price
 * @property Money|null $cost
 */
class MultiCurrencyModel extends Model
{
    protected $casts = [
        'amount' => MoneyCast::class.':currency_code',
        'price' => MoneyCast::class.':price_currency',
        'cost' => MoneyCast::class.':cost_currency',
    ];
}
