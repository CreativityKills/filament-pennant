<?php

declare(strict_types=1);

namespace CK\FilamentPennant;

use CK\FilamentPennant\Concerns\AllowsFeatureSegmentResourceSchemaCustomizations;

class FilamentPennant
{
    use AllowsFeatureSegmentResourceSchemaCustomizations;

    /**
     * @return \CK\FilamentPennant\Models\FeatureSegment
     */
    public function getFeatureSegmentModelInstance()
    {
        $modelClass = FilamentPennantPlugin::get()->getModel();

        return new $modelClass();
    }
}
