<?php

declare(strict_types=1);

namespace CK\FilamentPennant\Concerns;

use Illuminate\Support\Facades\Config;
use CK\FilamentPennant\Models\FeatureSegment;

trait ConfiguresFeatureSegmentResource
{
    protected ?string $model = null;

    protected ?string $navigationGroup = null;

    protected ?string $navigationLabel = null;

    protected ?string $modelLabel = null;

    protected ?string $navigationIcon = null;

    protected int $sort = 0;

    public function sort(int $sort = 0): static
    {
        $this->sort = $sort;

        return $this;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setModel(?string $model = null): static
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return class-string
     */
    public function getModel(): string
    {
        return $this->model ?? Config::get('filament-pennant.feature-segment.model', FeatureSegment::class);
    }

    public function setNavigationGroup(?string $group = null): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function getNavigationGroup(): ?string
    {
        return $this->navigationGroup ?? Config::get('filament-pennant.feature-segment.panel.group', 'Settings');
    }

    public function setNavigationLabel(?string $label = null): static
    {
        $this->navigationLabel = $label;

        return $this;
    }

    public function getNavigationLabel(): string
    {
        return $this->navigationLabel ?? Config::get('filament-pennant.feature-segment.panel.label', 'Manage Features');
    }

    public function setModelLabel(?string $label = null): static
    {
        $this->modelLabel = $label;

        return $this;
    }

    public function getModelLabel(): string
    {
        return $this->modelLabel ?? Config::get('filament-pennant.feature-segment.panel.title', 'Manage Features & Segments');
    }

    public function setNavigationIcon(?string $icon = null): static
    {
        $this->navigationIcon = $icon;

        return $this;
    }

    public function getNavigationIcon(): ?string
    {
        return $this->navigationIcon ?? Config::get('filament-pennant.feature-segment.panel.icon', 'heroicon-o-cursor-arrow-ripple');
    }
}
