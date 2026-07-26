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

        Auth::provider('legacy', function ($app, array $config) {
            return new LegacyUserProvider($app['hash'], $config['model']);
        });

        TextColumn::macro('translateFromConfig', function (string $configKey) {
            return $this->formatStateUsing(function ($state, \Illuminate\Database\Eloquent\Model $record) use ($configKey) {
                static $cache = [];
                $dbName = $record->getConnectionName();
                $cacheKey = "{$dbName}.{$configKey}.{$state}";

                if (isset($cache[$cacheKey])) {
                    return $cache[$cacheKey];
                }

                $configPath = config()->has("p.{$dbName}") 
                    ? "p.{$dbName}.{$configKey}.{$state}" 
                    : "p.default.{$configKey}.{$state}";

                $result = config($configPath, $state);
                $cache[$cacheKey] = $result;

                return $result;
            });
        });

        TextEntry::macro('translateFromConfig', function (string $configKey) {
            return $this->formatStateUsing(function ($state, \Illuminate\Database\Eloquent\Model $record) use ($configKey) {
                static $cache = [];
                $dbName = $record->getConnectionName();
                $cacheKey = "{$dbName}.{$configKey}.{$state}";

                if (isset($cache[$cacheKey])) {
                    return $cache[$cacheKey];
                }

                $configPath = config()->has("p.{$dbName}") 
                    ? "p.{$dbName}.{$configKey}.{$state}" 
                    : "p.default.{$configKey}.{$state}";

                $result = config($configPath, $state);
                $cache[$cacheKey] = $result;

                return $result;
            });
        });

        \Filament\Forms\Components\Select::macro('optionsFromConfig', function (string $configKey) {
            return $this->options(function (?\Illuminate\Database\Eloquent\Model $record) use ($configKey) {
                $dbName = $record ? $record->getConnectionName() : config('database.default');
                $configPath = config()->has("p.{$dbName}.{$configKey}") 
                    ? "p.{$dbName}.{$configKey}" 
                    : "p.default.{$configKey}";
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
