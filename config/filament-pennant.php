<?php

declare(strict_types=1);

return [
    // The default scope to use when no scope is provided
    'default_scope' => 'App\Models\User',

    'default_value' => false,

    'feature-segment' => [
        // Model used to store and retrieve feature segments
        'model' => CK\FilamentPennant\Models\FeatureSegment::class,

        'segments' => [
            // Modules\Guard\Features\RoleManagement::class => [

            //     // Define the compatible scopes...
            //     Modules\Organization\Models\Organization::class => [
            //         'name' => 'Organizations',
            //         'source' => [
            //             'model' => Modules\Organization\Models\Organization::class,
            //             'key_column' => 'id',
            //             'label_column' => 'name',
            //         ],
            //     ],

            // ],
        ],
    ],

    'resources' => [
        CK\FilamentPennant\Resources\FeatureSegmentResource::class,
    ]
];
