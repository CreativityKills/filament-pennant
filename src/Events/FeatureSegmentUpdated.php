<?php

declare(strict_types=1);

namespace CK\FilamentPennant\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FeatureSegmentUpdated implements ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param \CK\FilamentPennant\Models\FeatureSegment $featureSegment
     */
    public function __construct(
        public $featureSegment,
        public mixed $authUser
    ) {
    }
}
