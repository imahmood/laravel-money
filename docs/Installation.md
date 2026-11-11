# Installation

## Requirements

Before installing the package, make sure your application has:

- PHP 8.3 or newer
- The `decimal` PHP extension

## Install with Composer

Install the package from your Laravel application's directory:

```bash
composer require imahmood/laravel-money
```

Laravel automatically discovers the package service provider. No manual provider registration is required.

## Publish the configuration

To publish `config/money.php` into your application, run:

```bash
php artisan vendor:publish --provider="Imahmood\Money\MoneyServiceProvider"
```

After publishing the file, update the currencies and exchange rates to match your application. See the [Configuration](Configuration.md) guide for details.
