@php
    $database = session('tenant_database', config('academic_years.default_database'));
    $years = config('academic_years.databases', []);
    
    if (isset($years[$database])) {
        $displayYear = $years[$database];
    } elseif (preg_match('/(20\d{2})/', (string) $database, $matches)) {
        $y = (int) $matches[1];
        $displayYear = "{$y}-" . ($y - 1);
    } else {
        $displayYear = 'غير محدد';
    }
@endphp

<div class="flex items-center gap-x-2 px-4 rounded-full bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400 border border-primary-100 dark:border-primary-800 text-sm font-semibold py-1">
    {{-- <x-heroicon-o-calendar class="w-1 h-1" /> --}}
    <span>بوابة القبول  للعام : {{ $displayYear }}</span>
</div>
