<?php

declare(strict_types=1);

namespace CK\FilamentPennant\Resources;

use Filament\Actions;
use Laravel\Pennant\Feature;
use CK\FilamentPennant\Events;
use Filament\Facades\Filament;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use CK\FilamentPennant\FilamentPennantPlugin;
use CK\FilamentPennant\Facades\FilamentPennant;
use CK\FilamentPennant\Resources\FeatureSegmentResource;

class ManageFeatureSegments extends ManageRecords
{
    protected static string $resource = FeatureSegmentResource::class;

    protected function getHeaderActions(): array
    {
        $allFeaturesOptionsList = FilamentPennant::getFeatureSegmentModelInstance()
            ->allFeatures()
            ->pluck('name', 'id')
            ->all();

        $activateForEveryoneSchema = $deactivateForEveryoneSchema= [
           Select::make('feature')
               ->label(__('Feature'))
               ->required()
               ->options($allFeaturesOptionsList)
               ->columnSpanFull(),
        ];

        $purgeFeaturesSchema = [
            Select::make('feature')
                ->label(__('Feature'))
                ->selectablePlaceholder(false)
                ->options(array_merge([null => __('All Features')], $allFeaturesOptionsList))
                ->columnSpanFull(),
        ];

        return FilamentPennant::featureSegmentTableHeaderActions([
            Actions\CreateAction::make()
                ->modalWidth('md')
                ->modalHeading(__('Create Feature Segment'))
                ->label(__('Segment'))
                ->icon('heroicon-o-plus-circle')
                ->after(fn ($record) => $this->afterCreate($record)),

            ActionGroup::make([
                Actions\Action::make('activate_for_everyone')
                    ->label(__('Activate for everyone'))
                    ->modalWidth('md')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->color('danger')
                    ->modalDescription(fn () => __('This will activate the selected feature for everyone.'))
                    ->form($activateForEveryoneSchema)
                    ->modalSubmitActionLabel(__('Activate'))
                    ->action(fn ($data) => $this->activateForAll($data['feature'])),

                Actions\Action::make('deactivate_for_everyone')
                    ->label(__('Deactivate for everyone'))
                    ->modalWidth('md')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->color('danger')
                    ->modalDescription(fn () => __('This will deactivate the selected feature for everyone.'))
                    ->form($deactivateForEveryoneSchema)
                    ->modalSubmitActionLabel(__('Deactivate'))
                    ->action(fn ($data) => $this->deactivateForAll($data['feature'])),

                Actions\Action::make('purge_features')
                    ->label(__('Purge'))
                    ->modalWidth('md')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->color('danger')
                    ->modalDescription(fn () => __('This action will purge resolved features from storage.'))
                    ->form($purgeFeaturesSchema)
                    ->modalSubmitActionLabel(__('Purge'))
                    ->color('danger')
                    ->action(fn ($data) => $this->purgeFeatures($data['feature'])),
            ])
            ->color('gray')
            ->label(__('More'))
            ->button(),
        ]);
    }

    public static function canAccess(array $parameters = []): bool
    {
        return FilamentPennantPlugin::get()->authorized($parameters);
    }

    /**
     * @param class-string $feature
     */
    private function activateForAll(string $feature): void
    {
        Feature::activateForEveryone($feature);

        Notification::make()
            ->success()
            ->body(__(":title activated for scope.", ['title' => $feature::title()]))
            ->send();

        Events\FeatureActivatedForEveryone::dispatch($feature, Filament::auth()->user());
    }

    private function deactivateForAll(string $feature): void
    {
        Feature::deactivateForEveryone($feature);

        Notification::make()
            ->success()
            ->body(__(":title deactivated for everyone.", ['title' => $feature::title()]))
            ->send();

        Events\FeatureDeactivatedForEveryone::dispatch($feature, Filament::auth()->user());
    }

    private function purgeFeatures(?string $feature): void
    {
        Feature::purge($feature);

        $featureTitle = is_null($feature) ? __('All features') : $feature::title();

        Notification::make()
            ->success()
            ->body(__(":title successfully purged from storage.", ['title' => $featureTitle]))
            ->send();

        Events\FeaturePurged::dispatch($feature, Filament::auth()->user());
    }

    /**
     * @param \CK\FilamentPennant\Models\FeatureSegment $featureSegment
     */
    private function afterCreate($featureSegment): void
    {
        Feature::purge($featureSegment->feature);

        Events\FeatureSegmentCreated::dispatch($featureSegment, Filament::auth()->user());
    }
}
