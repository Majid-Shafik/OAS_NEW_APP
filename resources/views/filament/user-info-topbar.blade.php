@php
    $user = auth()->user();
    $userName = $user?->USER_NAME ?? $user?->LOGON_ID ?? 'مستخدم';
    
    // جلب اسم أول دور وظيفي للمستخدم من جدول الأدوار (Roles)
    $roleLabel = $user?->getRoleNames()?->first() 
        ?? $user?->roles?->first()?->name 
        ?? null;
@endphp

@if($user)
    <div style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: 13px; font-weight: 600;" class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg whitespace-nowrap">
        <span style="width: 8px; height: 8px; border-radius: 9999px;" class="bg-emerald-500 shrink-0"></span>
        <span>{{ $userName }}</span>
        @if($roleLabel)
            <span class="text-primary-600 dark:text-primary-400 font-bold">({{ $roleLabel }})</span>
        @endif
    </div>
@endif
