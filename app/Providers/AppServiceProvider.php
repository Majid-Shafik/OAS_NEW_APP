<?php

namespace App\Providers;

use App\Auth\LegacyUserProvider;
use App\Policies\ExportPolicy;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ColumnManagerLayout;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Export::class, ExportPolicy::class);
        Gate::policy(\Spatie\Activitylog\Models\Activity::class, \App\Policies\ActivityPolicy::class);

        Auth::provider('legacy', function ($app, array $config) {
            return new LegacyUserProvider($app['hash'], $config['model']);
        });

        $resolveConfigPath = function (?string $dbName, string $configKey, ?string $state = null) {
            $path = $state !== null ? "{$configKey}.{$state}" : $configKey;
            
            if ($dbName && config()->has("p.{$dbName}.{$configKey}")) {
                return "p.{$dbName}.{$path}";
            }

            if ($dbName && preg_match('/(20\d{2})/', $dbName, $m)) {
                $yearKey = "P_{$m[1]}";
                if (config()->has("p.{$yearKey}.{$configKey}")) {
                    return "p.{$yearKey}.{$path}";
                }
            }

            return "p.default.{$path}";
        };

        TextColumn::macro('translateFromConfig', function (string $configKey) use ($resolveConfigPath) {
            return $this->formatStateUsing(function ($state, \Illuminate\Database\Eloquent\Model $record) use ($configKey, $resolveConfigPath) {
                static $cache = [];
                $dbName = session('tenant_database', $record->getConnectionName() ?: config('database.connections.tenant.database'));
                $cacheKey = "{$dbName}.{$configKey}.{$state}";

                if (isset($cache[$cacheKey])) {
                    return $cache[$cacheKey];
                }

                $configPath = $resolveConfigPath($dbName, $configKey, (string) $state);
                $result = config($configPath, $state);
                $cache[$cacheKey] = $result;

                return $result;
            });
        });

        TextEntry::macro('translateFromConfig', function (string $configKey) use ($resolveConfigPath) {
            return $this->formatStateUsing(function ($state, \Illuminate\Database\Eloquent\Model $record) use ($configKey, $resolveConfigPath) {
                static $cache = [];
                $dbName = session('tenant_database', $record->getConnectionName() ?: config('database.connections.tenant.database'));
                $cacheKey = "{$dbName}.{$configKey}.{$state}";

                if (isset($cache[$cacheKey])) {
                    return $cache[$cacheKey];
                }

                $configPath = $resolveConfigPath($dbName, $configKey, (string) $state);
                $result = config($configPath, $state);
                $cache[$cacheKey] = $result;

                return $result;
            });
        });

        \Filament\Forms\Components\Select::macro('optionsFromConfig', function (string $configKey) use ($resolveConfigPath) {
            return $this->options(function (?\Illuminate\Database\Eloquent\Model $record) use ($configKey, $resolveConfigPath) {
                $dbName = session('tenant_database', $record ? $record->getConnectionName() : config('database.connections.tenant.database'));
                $configPath = $resolveConfigPath($dbName, $configKey, null);
                return config($configPath, []);
            });
        });

        // Force all numbers formatted by Laravel/Filament to use English digits (0-9)
        if (class_exists(Number::class)) {
            Number::useLocale('en');
        }
        $this->autoTranslateLabels();
        $this->configureToggleableComponents();
        $this->configureGlobalTableSettings();
        $this->configureExportActions();

        // Configure common formatting for TextColumns globally
        TextColumn::configureUsing(function (TextColumn $column): void {
            $column
                ->wrapHeader()
                ->alignCenter()
                ->toggleable(true);
        });

        \App\Models\ProgramCapacity::observe(\App\Observers\ProgramCapacityObserver::class);
    }

    private function autoTranslateLabels()
    {
        $this->translateLabels([
            Field::class,
            BaseFilter::class,
            Placeholder::class,
            Column::class,
            TextEntry::class,
        ]);
    }

    private function translateLabels(array $components = [])
    {
        foreach ($components as $component) {
            $component::configureUsing(function ($c): void {
                $name = $c->getName();
                if ($name) {
                    $c->label(__($name));
                }
            });
        }
    }

    private function configureToggleableComponents(): void
    {
        Column::configureUsing(function (Column $column): void {
            $column
                ->wrapHeader()
                ->alignCenter()
                ->toggleable(true);
        });
    }

    private function configureGlobalTableSettings(): void
    {
        Table::configureUsing(function (Table $table): void {
            $table
                ->persistSortInSession()
                ->striped()
                ->deferLoading()
                ->filtersLayout(FiltersLayout::AboveContentCollapsible)
                ->filtersFormColumns(6)
                ->defaultPaginationPageOption(25)
                ->reorderableColumns()
                ->deferColumnManager(false)
                ->columnManagerLayout(ColumnManagerLayout::Modal)
                ->columnManagerTriggerAction(fn (Action $action) => $action->slideover())
                ->paginationPageOptions([10, 25, 50, 100]);
        });
    }

    private function configureExportActions(): void
    {
        $configure = function ($action): void {
            $action->label('تصدير إكسل')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info');
        };

        if (class_exists(ExportAction::class)) {
            ExportAction::configureUsing($configure);
        }
    }
}
