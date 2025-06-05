<?php

declare(strict_types=1);

namespace CK\FilamentPennant;

use Closure;
use Filament\Panel;
use Filament\Contracts\Plugin;
use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Support\Facades\Config;
use CK\FilamentPennant\Concerns;

class FilamentPennantPlugin implements Plugin
{
    use EvaluatesClosures;
    use Concerns\ConfiguresFeatureSegmentResource;

    public Closure|bool $authorized = true;

    public function authorize(Closure|bool $condition = true): static
    {
        $this->authorized = $condition;

        return $this;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function authorized(array $parameters = []): bool
    {
        return $this->evaluate($this->authorized) === true;
    }

    public function getId(): string
    {
        return 'filament-pennant';
    }

    public function register(Panel $panel): void
    {
        $panel->resources(Config::array('filament-pennant.resources'));
    }

    public function boot(Panel $panel): void
    {
    }

    public static function make(): static
    {
        return resolve(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(resolve(static::class)->getId());

        return $plugin;
    }
}
