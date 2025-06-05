<?php

declare(strict_types=1);

namespace CK\FilamentPennant\Models;

use Laravel\Pennant\Feature;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

/**
 * CK\FilamentPennant\Models\FeatureSegment
 *
* @property string $feature
 * @property bool $active
 * @property string $scope
 * @property array<array-key, mixed> $values
 * @property string $title
 * @property string $description
 */
class FeatureSegment extends Model
{
    protected $guarded = [];

    // @phpstan-ignore-next-line
    protected $appends = ['title', 'description'];

    protected $casts = [
       'values' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, never>
     */
    public function title(): Attribute
    {
        return Attribute::get(function () {
            if (! class_exists($this->feature)) {
                return __('Unresolved Feature');
            }

            if (! method_exists($this->feature, 'title')) {
                return $this->feature;
            }

            return $this->feature::title();
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, never>
     */
    public function description(): Attribute
    {
        return Attribute::get(function () {
            return __(':feature :status for any of these :scope — :values.', [
                'feature' => $this->title,
                'status' => $this->active ? __('activated') : __('deactivated'),
                'scope' => str($this->scope)->plural()->value(),
                'values' => str(implode(', ', $this->values))->limit(50)->toString(),
            ]);
        });
    }

    /**
     * @return Collection<int, array{id: int|string, name: string, state: mixed, description: string}>
     */
    public static function allFeatures(): Collection
    {
        return collect(Feature::all())
            ->map(fn ($value, $key) => [
                'id' => $key,
                'name' => $name = str(class_basename($key))->snake()->replace('_', ' ')->title()->toString(),
                'state' => $value,
                'description' => "This feature covers $name on the mobile app.",
            ])
            ->values();
    }

    public static function allScopesConfig(?string $feature): Collection
    {
        return collect(Config::get(sprintf('filament-pennant.feature-segment.segments.%s', $feature)));
    }

    /**
     * @return Collection<string, string>
     */
    public static function allScopes(
        ?string $feature,
        ?callable $filter = null,
        ?string $key = null,
        ?string $label = null
    ): Collection {
        if (! $feature) {
            return collect();
        }

        return self::allScopesConfig($feature)
            ->when($filter, fn ($collection) => $collection->filter($filter))
            ->mapWithKeys(function (array $segment, string $model) use ($key, $label) {
                $label = $label ? data_get($segment, $label) : null;
                $providedKey = $key ? data_get($segment, $key) : null;

                if (! $label) {
                    $label = __($segment['name']);
                }

                if ($providedKey) {
                    return [$providedKey => $label];
                }

                if (isset($segment['source']['model'])) {
                    return [$segment['source']['model'] => $label];
                }

                return [$model => $label];
            });
    }

    public static function allScopesFlat(?string $key = null): Collection
    {
        $scopes = collect();
        $config = array_keys(Config::array('filament-pennant.feature-segment.segments'));

        foreach ($config as $feature) {
            $scopes = $scopes->merge(self::allScopes($feature, key: $key));
        }

        return $scopes;
    }

    /**
     * Determines whether this segment should activate for the given scope.
     *
     * For active segments: returns true if the scope's property value matches any of the segment's values.
     * For inactive segments: returns true if the scope's property value does NOT match any of the segment's values.
     *
     * This allows segments to either include or exclude specific scope values based on their active state.
     *
     * @param mixed $scope The scope object to evaluate (must have the property specified in $this->scope)
     * @return bool True if this segment should activate for the given scope, false otherwise
     */
    public function resolve(mixed $scope): bool
    {
        $scopePropertyValue = $scope->{$this->scope};
        $scopeMatchesSegmentValues = in_array($scopePropertyValue, $this->values, true);

        if ($this->active) {
            return $scopeMatchesSegmentValues;
        }

        // This could account for reverse activation of segments. For example, if a segment is deactivated,
        // it could be used to exclude a specific scope value. So we activate if the scope does not match
        // any of our values.
        return !$scopeMatchesSegmentValues;
    }
}
