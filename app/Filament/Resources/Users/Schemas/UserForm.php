<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Faculty;
use App\Models\Program;
use App\Models\University;
use App\Models\UserGroup;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->schema([
                Grid::make(12)
                    ->schema([
                        Section::make('المعلومات الأساسية')
                            ->schema([
                                Select::make('UNID')
                                    ->label('الجامعة')
                                    ->options(University::pluck('U_NAME', 'UNID')->prepend('غير محدد', 0))
                                    ->live()
                                    ->searchable()
                                    ->default(0),
                                Select::make('FACULTY_IDENT')
                                    ->label('الكلية')
                                    ->options(fn(Get $get) => Faculty::where('UNID', $get('UNID'))->pluck('FACULTY_NAME', 'FACULTY_IDENT')->prepend('غير محدد', 0))
                                    ->live()
                                    ->searchable()
                                    ->default(0),
                                Select::make('PROGRAM_IDENT')
                                    ->label('التخصص')
                                    ->options(fn(Get $get) => Program::where('UNID', $get('UNID'))
                                        ->where('FACULTY_IDENT', $get('FACULTY_IDENT'))
                                        ->pluck('PROGRAM_NAME', 'PROGRAM_IDENT')->prepend('غير محدد', 0))
                                    ->searchable()
                                    ->required()
                                    ->default(0),
                                TextInput::make('USER_NAME')
                                    ->label('اسم المستخدم')
                                    ->required(),
                                TextInput::make('LOGON_ID')
                                    ->label('اسم تسجيل الدخول')
                                    ->required(),
                                Select::make('GROUP_IDENT')
                                    ->label('المجموعة (الدور القديم)')
                                    ->options(UserGroup::pluck('GROUP_NAME', 'GROUP_IDENT'))
                                    ->disabled()
                                    ->dehydrated()
                                    ->searchable(),
                                Select::make('roles')
                                    ->relationship(
                                        name: 'roles',
                                        titleAttribute: 'label',
                                        modifyQueryUsing: function (Builder $query) {
                                            $user = auth()->user();

                                            // super_admin و admin يشوفوا كل شيء
                                            if ($user->hasRole(['super_admin', 'admin']) || $user->id == 1) {
                                                return;
                                            }

                                            // جلب الأدوار المسموحة من الجدول
                                            $allowedRoleIds = DB::table('role_manageable_roles')
                                                ->whereIn('role_id', $user->roles->pluck('id'))
                                                ->pluck('manageable_role_id');

                                            $query
                                                ->whereIn('id', $allowedRoleIds)
                                                ->whereNotIn('id', [1, 2, 3]);  // حماية إضافية
                                        }
                                    )
                                    ->multiple()
                                    ->maxItems(3)
                                    ->preload()
                                    ->required()
                                    ->searchable()
                                    ->label('الدور الوظيفي'),

                                TextInput::make('LOGON_PASS')
                                    ->label('كلمة المرور')
                                    ->password()
                                    ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                                    ->dehydrated(fn(?string $state): bool => filled($state))
                                    ->required(fn(string $operation): bool => $operation === 'create')
                                    ->hiddenOn('edit'),
                                TextInput::make('EMAIL')
                                    ->label('البريد الإلكتروني')
                                    ->email(),
                                TextInput::make('MOBILE_PHONE')
                                    ->label('رقم الجوال')
                                    ->required(),
                                Select::make('IDENT_TYPE')
                                    ->label('نوع الهوية')
                                    ->options(\App\Models\ComboValue::getOptionsByCode(7))
                                    ->searchable()
                                    ->required(),
                                TextInput::make('IDENT_NO')
                                    ->label('رقم الهوية')
                                    ->required(),
                                Select::make('GENDER')
                                    ->label('الجنس')
                                    ->options(\App\Models\ComboValue::getOptionsByCode(6))
                                    // ->searchable()
                                    ->required(),



                            ])
                            ->columns(3)
                            ->columnSpan(9),

                        Section::make('الحساب')
                            ->schema([
                                \Filament\Forms\Components\FileUpload::make('profile_photo_fake')
                                    ->label('صورة المستخدم')
                                    ->image()
                                    ->avatar()
                                    ->dehydrated(false) // لأن الصورة لن تحفظ في هذا الجدول
                                    ->helperText('الصورة للعرض فقط (يتم جلبها من مسار آخر)')
                                    ->nullable(),
                                Toggle::make('IS_IT_ENABLE')
                                    ->label('حالة الحساب')
                                    ->default(1)
                                    ->inline(false)
                                    ->required(),
                            ])
                            ->columnSpan(3)
                            ->extraAttributes([
                                'class' => 'sticky top-6',
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
