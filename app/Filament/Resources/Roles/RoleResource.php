<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Filament\Resources\Roles\Pages\ViewRole;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BezhanSalleh\FilamentShield\Support\Utils;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;
use BezhanSalleh\PluginEssentials\Concerns\Resource as Essentials;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use UnitEnum;
use Override;

class RoleResource extends Resource
{
    use Essentials\BelongsToParent;
    use Essentials\BelongsToTenant;
    use Essentials\HasGlobalSearch;
    use Essentials\HasLabels;
    use Essentials\HasNavigation;
    use HasShieldFormComponents;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canGloballySearch(): bool
    {
        return false;
    }


    #[Override]
    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'المستخدمين والصلاحيات';
    }
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (!auth()->user()->hasRole('super_admin')) {
            $query->where('name', '!=', 'super_admin');
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('RoleTabs')
                    ->tabs([
                        // ١. تبويب معلومات الدور
                        Tab::make('المعلومات الأساسية')
                            ->schema([
                                Grid::make(6)
                                    ->schema([
                                        Section::make()
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('الاسم البرمجي للدور (انجليزي)')
                                                    ->unique(
                                                        ignoreRecord: true,
                                                        modifyRuleUsing: fn(Unique $rule): Unique => Utils::isTenancyEnabled()
                                                            ? $rule->where(Utils::getTenantModelForeignKey(), Filament::getTenant()?->id)
                                                            : $rule
                                                    )
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->columnSpan(1)
                                                    ->disabled(fn($record) => $record && in_array($record->name, [
                                                        'super_admin',
                                                        'admin',
                                                        'safety_manager',
                                                        'project_officer'
                                                    ])),
                                                TextInput::make('label')
                                                    ->label('الاسم المعروض للدور (عربي)')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->columnSpan(1)
                                                    ->disabled(fn($record) => $record && in_array($record->name, [
                                                        'super_admin',
                                                        'admin',
                                                        'safety_manager',
                                                        'project_officer'
                                                    ])),
                                                TextInput::make('guard_name')
                                                    ->label('اسم الحارس (Guard)')
                                                    ->default(Utils::getFilamentAuthGuard())
                                                    ->nullable()
                                                    ->columnSpan(1)
                                                    ->maxLength(255),
                                                Select::make(config('permission.column_names.team_foreign_key'))
                                                    ->label(__('filament-shield::filament-shield.field.team'))
                                                    ->placeholder(__('filament-shield::filament-shield.field.team.placeholder'))
                                                    /** @phpstan-ignore-next-line */
                                                    ->default(Filament::getTenant()?->id)
                                                    ->options(fn(): array => in_array(Utils::getTenantModel(), [null, '', '0'], true) ? [] : Utils::getTenantModel()::pluck('name', 'id')->toArray())
                                                    ->visible(fn(): bool => static::shield()->isCentralApp() && Utils::isTenancyEnabled())
                                                    ->dehydrated(fn(): bool => static::shield()->isCentralApp() && Utils::isTenancyEnabled()),
                                                static::getSelectAllFormComponent(),
                                            ])
                                            ->columns(['sm' => 3, 'lg' => 4])
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull(),
                                static::getShieldFormComponents(),
                            ])
                            ->icon('heroicon-o-shield-check'),
                        // ٣. تبويب الأدوار القابلة للإدارة
                        // تبويب Manageable Roles
                        Tab::make('الأدوار المسموح بإدارتها')
                            ->badge(function ($record) {
                                return $record ? $record->manageableRoles->count() : null;
                            })
                            ->schema([
                                CheckboxList::make('manageable_roles')
                                    ->label('اختر الأدوار')
                                    ->relationship('manageableRoles', 'name')
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->label ?: $record->name)
                                    // ->relationship('manageableRoles', 'label')  // تأكد من العلاقة في Role model
                                    ->columns(3)
                                    ->bulkToggleable()
                                    ->searchable()
                                    ->afterStateHydrated(function ($component, $record) {
                                        if ($record) {
                                            $component->state(
                                                $record->manageableRoles->pluck('id')->toArray()
                                            );
                                        }
                                    }),
                            ])
                            ->icon('heroicon-o-lock-closed'),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->searchable()->sortable(),
                TextColumn::make('name')
                    ->weight(FontWeight::Medium)
                    ->label(__('filament-shield::filament-shield.column.name'))
                    ->formatStateUsing(fn(string $state): string => Str::headline($state))
                    ->searchable()
                    ->copyable()
                    ->copyMessage('تم النسخ!'),
                TextColumn::make('label')
                    ->label('الوصف'),
                TextColumn::make('guard_name')
                    ->badge()
                    ->color('warning')
                    ->label(__('filament-shield::filament-shield.column.guard_name')),
                TextColumn::make('permissions_count')
                    ->badge()
                    ->label(__('filament-shield::filament-shield.column.permissions'))
                    ->counts('permissions')
                    ->color('primary'),
                TextColumn::make('updated_at')
                    ->label(__('filament-shield::filament-shield.column.updated_at'))
                    ->dateTime(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                \Filament\Actions\Action::make('syncSingleRole')
                    ->label('نشر الدور')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('info')
                    ->visible(fn () => auth()->user()?->isAdmin())
                    ->modalHeading(fn ($record) => "نشر الدور [{$record->name}] إلى قواعد بيانات أخرى")
                    ->modalDescription('اختر قواعد البيانات التي ترغب في نسخ وتحديث هذا الدور وصلاحياته إليها.')
                    ->modalSubmitActionLabel('بدء النشر')
                    ->form([
                        CheckboxList::make('target_databases')
                            ->label('قواعد البيانات المستهدفة')
                            ->options(fn () => \App\Services\RolePermissionSyncService::getAvailableTargetDatabases())
                            ->default(fn () => array_keys(\App\Services\RolePermissionSyncService::getAvailableTargetDatabases()))
                            ->bulkToggleable()
                            ->searchable()
                            ->required()
                            ->minItems(1, 'يجب اختيار قاعدة بيانات واحدة على الأقل.'),

                        Toggle::make('sync_manageable_roles')
                            ->label('مزامنة الأدوار المسموح بإدارتها لهذا الدور')
                            ->default(true),
                    ])
                    ->action(function ($record, array $data, \App\Services\RolePermissionSyncService $syncService): void {
                        $result = $syncService->sync(
                            targetDatabases: $data['target_databases'] ?? [],
                            roleIds: [$record->id],
                            syncUserRoles: true,
                            syncManageableRoles: (bool) ($data['sync_manageable_roles'] ?? true),
                            overwrite: true
                        );

                        if ($result['success']) {
                            \Filament\Notifications\Notification::make()
                                ->title('تم نشر الدور بنجاح')
                                ->body($result['message'])
                                ->success()
                                ->send();
                        } else {
                            $errorDetails = [];
                            foreach ($result['errors'] as $db => $err) {
                                $errorDetails[] = "{$db}: {$err}";
                            }
                            $body = $result['message'];
                            if (!empty($errorDetails)) {
                                $body .= "\n\nالسبب: " . implode("\n", $errorDetails);
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('تنبيه أثناء النشر')
                                ->body($body)
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'view' => ViewRole::route('/{record}'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }

    public static function getModel(): string
    {
        return Utils::getRoleModel();
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return Utils::getResourceSlug();
    }

    public static function getCluster(): ?string
    {
        return Utils::getResourceCluster();
    }

    public static function getEssentialsPlugin(): ?FilamentShieldPlugin
    {
        return FilamentShieldPlugin::get();
    }
}
