# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Filament Pennant (`creativitykills/filament-pennant`) is a Laravel package that integrates Laravel Pennant feature flags into the Filament Admin Panel. It provides a UI for managing class-based feature flags with scoped segments (e.g., enabling a feature for specific users or teams).

## Common Commands

```bash
composer test                # Run Pest test suite
composer test-coverage       # Run tests with coverage report
composer analyse             # Run PHPStan static analysis (level 5)
composer format              # Format code with Laravel Pint
vendor/bin/pest --filter="test name"  # Run a single test
```

## Architecture

**Namespace:** `CK\FilamentPennant\`

### Entry Points
- **FilamentPennantServiceProvider** — Package bootstrap via Spatie's PackageServiceProvider. Registers config, migrations, and auto-discovers class-based features.
- **FilamentPennantPlugin** — Filament Plugin interface implementation. Registers the FeatureSegmentResource with Filament panels and provides fluent configuration (authorization, navigation, labels).
- **FilamentPennant facade** — Public API surface for schema customizations and model access.

### Core Flow
1. Feature classes (in `App\Features` or custom locations registered via `FilamentPennantServiceProvider::registerCustomFeatureLocations()`) use the `ResolvesFeatureSegments` trait
2. The `FeatureSegment` model stores feature/scope/values combinations in the `featuresegments` table with a unique constraint on `[feature, scope, active]`
3. `ResolvesFeatureSegments::resolve()` queries active segments to determine if a feature applies to a given scope, falling back to `resolveDefaultValue()` from config
4. The Filament resource (`FeatureSegmentResource` + `ManageFeatureSegments` page) provides the admin UI for CRUD operations plus bulk activate/deactivate/purge actions

### Traits (src/Concerns/)
- **ResolvesFeatureSegments** — Used in feature classes; handles database-backed feature resolution
- **ConfiguresFeatureSegmentResource** — Used by the Plugin; holds navigation/model configuration
- **AllowsFeatureSegmentResourceSchemaCustomizations** — Used by the Facade; enables closure-based modifications to form fields, table columns, filters, and actions

### Events
All events implement `ShouldDispatchAfterCommit`. Dispatched for: segment create/update/delete, activate/deactivate for everyone, and purge. Each event carries the feature/segment data and the authenticated user.

## Key Conventions

- All files declare `strict_types=1`
- Events use past tense naming (e.g., `FeatureSegmentCreated`)
- Configuration drives feature-to-scope mappings — scopes are defined per-feature in `config/filament-pennant.php` under `feature-segment.segments`
- The package supports Laravel 10/11/12 and PHP 8.3/8.4
- Testing uses Pest with Orchestra Testbench (SQLite in-memory database)
