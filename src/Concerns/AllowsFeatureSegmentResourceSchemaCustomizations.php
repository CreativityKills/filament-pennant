<?php

declare(strict_types=1);

namespace CK\FilamentPennant\Concerns;

use Closure;

trait AllowsFeatureSegmentResourceSchemaCustomizations
{
    protected static ?Closure $featureSegmentFormComponentsHandler = null;

    protected static ?Closure $featureSegmentTableColumnsHandler = null;

    protected static ?Closure $featureSegmentTableFiltersHandler = null;

    protected static ?Closure $featureSegmentTableActionsHandler = null;

    protected static ?Closure $featureSegmentTableHeaderActionsHandler = null;

    public static function modifyFeatureSegmentFormComponents(callable $callback): void
    {
        static::$featureSegmentFormComponentsHandler = $callback;
    }

    public static function modifyFeatureSegmentTableColumns(callable $callback): void
    {
        static::$featureSegmentTableColumnsHandler = $callback;
    }

    public static function modifyFeatureSegmentTableFilters(callable $callback): void
    {
        static::$featureSegmentTableFiltersHandler = $callback;
    }

    public static function modifyFeatureSegmentTableActions(callable $callback): void
    {
        static::$featureSegmentTableActionsHandler = $callback;
    }

    public static function modifyFeatureSegmentTableHeaderActions(callable $callback): void
    {
        static::$featureSegmentTableHeaderActionsHandler = $callback;
    }

    /**
     * @param  array<\Filament\Forms\Components\Component>  $components
     */
    public static function featureSegmentFormComponents(array $components): array
    {
        if (static::$featureSegmentFormComponentsHandler) {
            return call_user_func(static::$featureSegmentFormComponentsHandler, $components);
        }

        return $components;
    }

    /**
     * @param  array<\Filament\Tables\Columns\Column>  $columns
     */
    public static function featureSegmentTableColumns(array $columns): array
    {
        if (static::$featureSegmentTableColumnsHandler) {
            return call_user_func(static::$featureSegmentTableColumnsHandler, $columns);
        }

        return $columns;
    }

    /**
     * @param  array<\Filament\Tables\Actions\Action>  $actions
     */
    public static function featureSegmentTableActions(array $actions): array
    {
        if (static::$featureSegmentTableActionsHandler) {
            return call_user_func(static::$featureSegmentTableActionsHandler, $actions);
        }

        return $actions;
    }

    /**
     * @param  array<\Filament\Tables\Filters\Filter>  $filters
     */
    public static function featureSegmentTableFilters(array $filters): array
    {
        if (static::$featureSegmentTableFiltersHandler) {
            return call_user_func(static::$featureSegmentTableFiltersHandler, $filters);
        }

        return $filters;
    }

    /**
     * @param  array<\Filament\Tables\Actions\Action|\Filament\Tables\Actions\ActionGroup>  $actions
     */
    public static function featureSegmentTableHeaderActions(array $actions): array
    {
        if (static::$featureSegmentTableHeaderActionsHandler) {
            return call_user_func(static::$featureSegmentTableHeaderActionsHandler, $actions);
        }

        return $actions;
    }
}
