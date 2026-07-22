<?php

namespace App\Providers;

use App\Auth\LegacyUserProvider;
use Filament\Actions\Action;
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
        Auth::provider('legacy', function ($app, array $config) {
            return new LegacyUserProvider($app['hash'], $config['model']);
        });

        // Force all numbers formatted by Laravel/Filament to use English digits (0-9)
        if (class_exists(Number::class)) {
            Number::useLocale('en');
        }
        $this->autoTranslateLabels();
        $this->configureToggleableComponents();
        $this->configureGlobalTableSettings();

        // Configure common formatting for TextColumns globally
        TextColumn::configureUsing(function (TextColumn $column): void {
            $column
                ->wrapHeader()
                ->alignCenter()
                ->toggleable(true);
        });
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
}
