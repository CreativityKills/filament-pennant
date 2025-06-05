<?php

declare(strict_types=1);

namespace CK\FilamentPennant\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FeatureActivatedForEveryone implements ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param class-string $feature
     */
    public function __construct(
        public string $feature,
        public mixed $authUser
    ) {
    }
}
