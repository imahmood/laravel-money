<?php
declare(strict_types=1);

namespace Imahmood\Money;

use Illuminate\Support\ServiceProvider;

class MoneyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/money.php' => config_path('money.php'),
        ]);
    }
}
