<?php

declare(strict_types=1);

namespace CK\FilamentPennant\Concerns;

use Laravel\Pennant\Feature;
use Illuminate\Support\Facades\Config;
use CK\FilamentPennant\Facades\FilamentPennant;

trait ResolvesFeatureSegments
{
    public function resolve(mixed $scope): bool
    {
        $featureScope = method_exists($this, 'scope') ? $this->scope() : Config::get('filament-pennant.default_scope');

        if (! class_exists($featureScope) || ! is_a($scope, $featureScope)) {
            return $this->resolveDefaultValue($scope);
        }

        $segments = FilamentPennant::getFeatureSegmentModelInstance()
            ->query()
            ->where('feature', get_class($this))
            ->get()
            ->all();

        if (array_any($segments, fn ($segment) => $segment->resolve($scope))) {
            return true;
        }

        return $this->resolveDefaultValue($scope);
    }

    protected function resolveDefaultValue(mixed $scope): bool
    {
        if (property_exists($this, 'defaultValue')) {
            return (bool) $this->defaultValue;
        }

        if (method_exists($this, 'defaultValue')) {
            return (bool) $this->defaultValue($scope);
        }

        return Config::get('filament-pennant.default_value');
    }

    public static function title(): string
    {
        return str(class_basename(self::class))->headline()->toString();
    }

    public static function description(): string
    {
        return __('This feature handles :name.', ['name' => self::title()]);
    }

    public static function state(): bool
    {
        return Feature::active(self::class);
    }
}
