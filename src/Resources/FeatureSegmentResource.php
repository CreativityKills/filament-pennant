<?php

declare(strict_types=1);

namespace CK\FilamentPennant\Resources;

use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;
use Filament\Notifications\Notification;
use CK\FilamentPennant\FilamentPennantPlugin;
use CK\FilamentPennant\Facades\FilamentPennant;
use CK\FilamentPennant\Events\FeatureSegmentDeleted;
use CK\FilamentPennant\Events\FeatureSegmentUpdated;
use CK\FilamentPennant\Events\FeatureSegmentDeleting;

class FeatureSegmentResource extends Resource
{
    public static function form(Form $form): Form
    {
        $allFeatures = FilamentPennant::getFeatureSegmentModelInstance()->allFeatures();

        $schema = FilamentPennant::featureSegmentFormComponents([
            Select::make('feature')
                ->label(__('Select Feature'))
                ->live()
                ->required()
                ->options($allFeatures->pluck('name', 'id')->all())
                ->columnSpanFull(),

            Select::make('scope')
                ->label(__('Select Scope'))
                ->live()
                ->visible(fn (Get $get) => $get('feature'))
                ->required()
                ->columnSpanFull()
                ->afterStateUpdated(fn (Set $set) => $set('values', null))
                ->options(fn (Get $get) => FilamentPennant::getFeatureSegmentModelInstance()
                    ->allScopes($get('feature'))
                    ->all()),

            ...self::createSelectValuesFields(),

            Select::make('active')
                ->label(__('Status'))
                ->options([true => 'Activate', false => 'Deactivate'])
                ->visible(fn (Get $get) => $get('feature') && $get('scope'))
                ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, Get $get) => (
                    $rule->where('feature', $get('feature'))->where('scope', $get('scope'))->where('active', $get('active'))
                ))
                ->validationMessages([
                    'unique' => __('Feature segmentation already exists.'),
                ])
                ->required()
                ->columnSpanFull(),
        ]);

        return $form->schema($schema);
    }

    /**
     * @return array<Select>
     */
    private static function createSelectValuesFields(): array
    {
        $fields = [];

        foreach (Config::get('filament-pennant.feature-segment.segments') as $feature => $segments) {
            foreach ($segments as $_model => $segment) {
                $value = $segment['source']['label_column'];
                $model = $segment['source']['model'] ?? $_model;

                $fields[] = Select::make('values')
                    ->label($segment['name'] ?? __('Unnamed Scope'))
                    ->hidden(fn (Get $get) => $get('scope') !== $model || $get('feature') !== $feature)
                    ->required()
                    ->multiple()
                    ->searchable()
                    ->columnSpanFull()
                    ->getSearchResultsUsing(fn (string $search): array => $model::query()
                        ->whereLike($value, "%{$search}%")
                        ->limit(50)
                        ->pluck($value, $segment['source']['key_column'])
                        ->toArray());
            }
        }

        return $fields;
    }

    public static function table(Table $table): Table
    {
        $columns = FilamentPennant::featureSegmentTableColumns([
            Tables\Columns\TextColumn::make('title')
                ->label(__('Feature'))
                ->sortable(['feature'])
                ->verticallyAlignStart()
                ->searchable(['feature']),

            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->label(__('Status'))
                ->verticallyAlignStart()
                ->color(fn (string $state): string => match ($state) {
                    __('Activated') => 'success',
                    __('Deactivated') => 'danger',
                    default => Color::Gray,
                })
                ->weight(FontWeight::ExtraBold)
                ->getStateUsing(fn ($record) => $record->getAttribute('active') ? __('Activated') : __('Deactivated')),

            Tables\Columns\TextColumn::make('values')
                ->label(__('Segment'))
                ->wrap()
                ->badge()
                ->verticallyAlignStart()
                ->getStateUsing(function ($record) {
                    $instance = class_exists($record['scope']) ? new $record['scope'] : null;
                    if (! $instance || ! $instance instanceof Model) {
                        return $record->getAttribute('values');
                    }

                    $source = FilamentPennant::getFeatureSegmentModelInstance()
                        ->allScopes($record['feature'], key: 'source.label_column', label: 'source.key_column')
                        ->map(fn (string $key, string $label) => [$key, $label])
                        ->first();

                    if (empty($source)) {
                        return $record->getAttribute('values');
                    }

                    [$keyColumn, $labelColumn] = $source;

                    return $instance->query()
                        ->whereIn($keyColumn, $record->getAttribute('values'))
                        ->pluck($labelColumn)
                        ->toArray();
                }),
        ]);

        $filters = FilamentPennant::featureSegmentTableFilters([
            Tables\Filters\SelectFilter::make('feature')
                ->options(FilamentPennant::getFeatureSegmentModelInstance()->allFeatures()->pluck('name', 'id')->all())
                ->label(__('Feature')),
            Tables\Filters\SelectFilter::make('scope')
                ->options(FilamentPennant::getFeatureSegmentModelInstance()->allScopesFlat(key: 'source.key_column')->all())
                ->label(__('Segment Scope')),
        ]);

        $actions = FilamentPennant::featureSegmentTableActions([
            Tables\Actions\EditAction::make()
                ->button()
                ->after(fn ($record) => FeatureSegmentUpdated::dispatch($record, Filament::auth()->user())),
            Tables\Actions\DeleteAction::make()
                ->button()
                ->successNotification(fn () => Notification::make()->success()->title(__('Segment deleted successfully')))
                ->before(fn ($record) => FeatureSegmentDeleting::dispatch($record, Filament::auth()->user()))
                ->after(fn () => FeatureSegmentDeleted::dispatch(Filament::auth()->user())),
        ]);

        return $table
            ->columns($columns)
            ->defaultSort('feature')
            ->filters($filters)
            ->actions($actions);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFeatureSegments::route('/'),
        ];
    }

    public static function getModel(): string
    {
        return FilamentPennantPlugin::get()->getModel();
    }

    public static function getNavigationSort(): int
    {
        return FilamentPennantPlugin::get()->getSort();
    }

    public static function getNavigationGroup(): ?string
    {
        return FilamentPennantPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationLabel(): string
    {
        return FilamentPennantPlugin::get()->getNavigationLabel();
    }

    public static function getModelLabel(): string
    {
        return FilamentPennantPlugin::get()->getModelLabel();
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentPennantPlugin::get()->getNavigationIcon();
    }
}
