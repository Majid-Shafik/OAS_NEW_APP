<?php

namespace App\Providers;

use App\Auth\LegacyUserProvider;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Facades\Auth;
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
        if (class_exists(\Illuminate\Support\Number::class)) {
            \Illuminate\Support\Number::useLocale('en');
        }
        $this->autoTranslateLabels();
        $this->configureToggleableComponents();
        $this->configureGlobalTableSettings();

        // Configure common formatting for TextColumns globally
        \Filament\Tables\Columns\TextColumn::configureUsing(function (\Filament\Tables\Columns\TextColumn $column): void {
            $column
                ->wrapHeader()
                ->alignCenter()
                ->toggleable(true);
        });
    }

    private function autoTranslateLabels()
    {
        $this->translateLabels([
            \Filament\Forms\Components\Field::class,
            \Filament\Tables\Filters\BaseFilter::class,
            Placeholder::class,
            \Filament\Tables\Columns\Column::class,
            \Filament\Infolists\Components\TextEntry::class
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
        \Filament\Tables\Columns\Column::configureUsing(function (\Filament\Tables\Columns\Column $column): void {
            $column
                ->wrapHeader()
                ->alignCenter()
                ->toggleable(true);
        });
    }

    private function configureGlobalTableSettings(): void
    {
        \Filament\Tables\Table::configureUsing(function (\Filament\Tables\Table $table): void {
            $table
                ->striped()
                ->deferLoading()
                ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContentCollapsible)
                ->filtersFormColumns(6)
                ->defaultPaginationPageOption(25)
                ->reorderableColumns()
                ->deferColumnManager(false)
                ->columnManagerLayout(\Filament\Tables\Enums\ColumnManagerLayout::Modal)
                ->columnManagerTriggerAction(fn(\Filament\Actions\Action $action) => $action->slideover())
                ->paginationPageOptions([10, 25, 50, 100]);
        });
    }
}
