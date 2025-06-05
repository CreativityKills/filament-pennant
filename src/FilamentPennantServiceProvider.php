<?php

declare(strict_types=1);

namespace CK\FilamentPennant;

use Laravel\Pennant\Feature;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentPennantServiceProvider extends PackageServiceProvider
{
    /**
     * @var array<string, string>
     */
    protected static array $customFeatureLocations = [];

    /**
     * You can register custom feature locations here and they will be used to automatically
     * discover class based features.
     *
     * @param  array<string, string>  $customFeatureLocations
     * @example
     * [
     *     '\Modules\ModuleName\Features' => '/path/to/module/Features',
     * ]
     */
    public static function registerCustomFeatureLocations(array $customFeatureLocations): void
    {
        self::$customFeatureLocations = $customFeatureLocations;
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-pennant')
            ->hasConfigFile()
            ->hasMigration('create_filament_pennant_table');
    }

    public function bootingPackage(): void
    {
        Feature::discover();

        foreach (static::$customFeatureLocations as $namespace => $path) {
            Feature::discover($namespace, $path);
        }
    }
}
