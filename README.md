# Filament Pennant – Manage Laravel Pennant in FilamentPHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/creativitykills/filament-pennant.svg?style=flat-square)](https://packagist.org/packages/creativitykills/filament-pennant)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/creativitykills/filament-pennant/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/creativitykills/filament-pennant/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/creativitykills/filament-pennant/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/creativitykills/filament-pennant/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/creativitykills/filament-pennant.svg?style=flat-square)](https://packagist.org/packages/creativitykills/filament-pennant)

This plugin is heavily inspired by [this plugin](https://github.com/stephenjude/filament-feature-flags). There are several improvements including but not limited to:

- Support for [scoped feature flags](https://laravel.com/docs/12.x/pennant#specifying-the-scope)
- Support for Features in custom directories
- and more...

![](https://raw.githubusercontent.com/creativitykills/filament-pennant/main/art/screenshot-1.png)

## Installation

You can install the package via composer:

```bash
composer require creativitykills/filament-pennant
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-pennant-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-pennant-config"
```

## Usage

> This package is exclusively for class based features.

You'll have to register the plugin in your panel provider.

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentPennantPlugin::make()
                ->authorize(fn () => auth()->user()->can('view.features'))
                // ...additional configuration available
                ->setNavigationGroup(__('Developer'))
                ->setNavigationLabel(__('Feature Segments'))
                ->setModelLabel(__('Feature Segments')),
        ]);
}
```

> You don't have to call `Feature::discover()` in your service provider boot method, this package already does this for you. However,
> if you have your features in custom locations outside the `App\Features` directory, you need to register them using:
>
> `FilamentPennantServiceProvider::registerCustomFeatureLocations(['Modules\ACL\Features' => '/var/www/html/modules/ACL/Features']);`

## Create Class Based Feature

To create a class based feature, you may invoke the pennant:feature Artisan command.

```bash
php artisan pennant:feature WalletFunding
```

When writing a feature class, you only need to use the `CK\FilamentPennant\Concerns\ResolvesFeatureSegments`
trait, which will be invoked to resolve the feature's initial value for a given scope.

```php
<?php

namespace App\Features;

use Laravel\Pennant\Feature;
use Modules\Organization\Models\Organization;
use CK\FilamentPennant\Concerns\ResolvesFeatureSegments;

class RoleManagement
{
    use ResolvesFeatureSegments;

    // You can optionally specify the scope of the feature
    // public function scope(): string
    // {
    //     return User::class;
    // }
}
```

You can see the trait for more things you can override like the `defaultValue` property.


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Neo Ighodaro](https://github.com/neoighodaro)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
