<?php

declare(strict_types=1);

namespace CK\FilamentPennant\Facades;

use CK\FilamentPennant\FilamentPennant as FilamentPennantFacadeAccessor;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \CK\FilamentPennant\Models\FeatureSegment getFeatureSegmentModelInstance()
 * @method static array featureSegmentFormComponents(array $components)
 * @method static array featureSegmentTableColumns(array $columns)
 * @method static array featureSegmentTableFilters(array $filters)
 * @method static array featureSegmentTableActions(array $actions)
 * @method static array featureSegmentTableHeaderActions(array $actions)
 */
class FilamentPennant extends Facade
{
    protected static function getFacadeAccessor()
    {
        return FilamentPennantFacadeAccessor::class;
    }
}
