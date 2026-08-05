<?php

namespace App\Livewire;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AcademicYearSwitcher extends Component
{
    public $selectedDatabase;

    public function mount()
    {
        $this->selectedDatabase = session('tenant_database', config('academic_years.default_database'));
    }

    public function canSwitch(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return (int) $user->UNID === 0;
    }

    public function updatedSelectedDatabase($value)
    {
        $databases = config('academic_years.databases', []);
        $previousDb = session('tenant_database', config('academic_years.default_database'));

        if (! $this->canSwitch()) {
            Notification::make()
                ->title('غير مصرح')
                ->body('ليس لديك صلاحية لتغيير العام الدراسي.')
                ->danger()
                ->send();

            $this->selectedDatabase = $previousDb;
            return;
        }

        if (! array_key_exists($value, $databases)) {
            $this->selectedDatabase = $previousDb;
            return;
        }

        $currentUser = auth()->user();
        $targetLabel = $databases[$value] ?? $value;
        $previousLabel = $databases[$previousDb] ?? $previousDb;

        // التحقق المسبق من وجود الحساب وتفعيله في قاعدة العام المستهدف
        try {
            $targetUser = DB::table($value . '.users')
                ->where('LOGON_ID', $currentUser->LOGON_ID)
                ->first();

            if (! $targetUser || (int) $targetUser->IS_IT_ENABLE !== 1) {
                // إبقاء المستخدم في عامه الحالي وعدم تغيير الجلسة
                $this->selectedDatabase = $previousDb;
                session(['tenant_database' => $previousDb]);

                Notification::make()
                    ->title('تعذر الانتقال للعام الدراسي')
                    ->body("عفواً، حسابك غير مسجل أو غير مفعل في عام ({$targetLabel}). تم إبقاؤك في عامك الحالي ({$previousLabel}).")
                    ->warning()
                    ->duration(7000)
                    ->send();

                return $this->redirect(request()->header('Referer') ?? '/admin');
            }

            // التحقق من الصلاحيات وتحديث الجلسة بالـ USER_IDENT المناسب للعام الجديد
            Config::set('database.connections.tenant.database', $value);
            Config::set('database.connections.mysql.database', $value);
            DB::purge('tenant');
            DB::purge('mysql');
            DB::setDefaultConnection('tenant');

            $targetUserModel = User::find($targetUser->USER_IDENT);

            if (! $targetUserModel || ! $targetUserModel->canAccessPanel(filament()->getCurrentOrDefaultPanel())) {
                // إعادة الاتصال بالعام السابق
                Config::set('database.connections.tenant.database', $previousDb);
                Config::set('database.connections.mysql.database', $previousDb);
                DB::purge('tenant');
                DB::purge('mysql');
                DB::setDefaultConnection('tenant');

                $this->selectedDatabase = $previousDb;
                session(['tenant_database' => $previousDb]);

                Notification::make()
                    ->title('صلاحيات غير كافية')
                    ->body("ليس لديك صلاحية دخول لوحة التحكم في عام ({$targetLabel}). تم إبقاؤك في العام الحالي ({$previousLabel}).")
                    ->danger()
                    ->duration(7000)
                    ->send();

                return $this->redirect(request()->header('Referer') ?? '/admin');
            }

            // الحساب سليم ومصرح: نقوم بتسجيل الدخول به في قاعدة العام الجديد وتحديث الجلسة
            session(['tenant_database' => $value]);
            Auth::login($targetUserModel);

            $this->redirect(request()->header('Referer') ?? '/admin');

        } catch (\Throwable $e) {
            // في حال حدوث أي خطأ، نعيد المستخدم فوراً لقاعدته السابقة بأمان
            Config::set('database.connections.tenant.database', $previousDb);
            Config::set('database.connections.mysql.database', $previousDb);
            DB::purge('tenant');
            DB::purge('mysql');
            DB::setDefaultConnection('tenant');

            $this->selectedDatabase = $previousDb;
            session(['tenant_database' => $previousDb]);

            Notification::make()
                ->title('تعذر التبديل')
                ->body("حدث خطأ أثناء فحص قاعدة البيانات للعام المطلوب.")
                ->danger()
                ->duration(7000)
                ->send();
        }
    }

    public function render()
    {
        $databases = config('academic_years.databases', []);

        return view('livewire.academic-year-switcher', [
            'databases' => $databases,
            'canSwitch' => $this->canSwitch(),
            'currentLabel' => $databases[$this->selectedDatabase] ?? $this->selectedDatabase,
        ]);
    }
}
