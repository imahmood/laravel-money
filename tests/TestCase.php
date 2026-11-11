<?php
declare(strict_types=1);

namespace Imahmood\Money\Tests;

use Imahmood\Money\MoneyServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function expectExactExceptionMessage(string $message): void
    {
        $this->expectExceptionMessageMatches('/\\A'.preg_quote($message, '/').'\\z/');
    }

    /**
     * Add the package provider.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getPackageProviders($app): array
    {
        return [MoneyServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        /** @var \Illuminate\Config\Repository $config */
        $config = $app['config'];

        $config->set('money', require __DIR__.'/../config/money.php');
        $config->set('database.default', 'testbench');
        $config->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
