@php
    $database = session('tenant_database');
    $years = [
        'p_oas_db_2022' => '2021-2022',
        'p_oas_db_2021' => '2020-2021',
        'p_oas_db_2020' => '2019-2020',
        'p_oas_db_2019' => '2018-2019',
    ];
    $displayYear = $years[$database] ?? 'غير محدد';
@endphp

<div class="flex items-center gap-x-2 px-4 rounded-full bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400 border border-primary-100 dark:border-primary-800 text-sm font-semibold py-1">
    {{-- <x-heroicon-o-calendar class="w-1 h-1" /> --}}
    <span>بوابة القبول  للعام : {{ $displayYear }}</span>
</div>
