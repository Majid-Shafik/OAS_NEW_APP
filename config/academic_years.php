<?php

declare(strict_types=1);

$prefix = env('DB_DATABASE_PREFIX', '');
// إذا لم يتم تحديد بادئة صريحة، يتم استكشاف ما إذا كانت قاعدة البيانات الأساسية تبدأ بـ test_ أو بادئة أخرى
if (empty($prefix) && env('DB_DATABASE')) {
    $mainDb = (string) env('DB_DATABASE');
    if (str_starts_with($mainDb, 'test_')) {
        $prefix = 'test_';
    }
}

$rawDatabases = [
    'p_oas_db_2022' => 'اهلي 2021-2022',
    'p_oas_db_2021' => 'اهلي 2020-2021',
    'p_oas_db_2020' => 'اهلي 2019-2020',
    'p_oas_db_2019' => 'اهلي 2018-2019',
    'p_oas_db_2018' => 'اهلي 2017-2018',
    'g_oas_db_2019' => 'حكومي 2018-2019',
];

$databases = [];
foreach ($rawDatabases as $dbKey => $label) {
    $fullDbName = $prefix ? ($prefix . $dbKey) : $dbKey;
    $databases[$fullDbName] = $label;
}

$defaultDb = env('DEFAULT_ACADEMIC_DATABASE', env('DB_DATABASE', ($prefix ? $prefix . 'p_oas_db_2022' : 'p_oas_db_2022')));

return [
    /*
    |--------------------------------------------------------------------------
    | Academic Years & Database Connections Configuration
    |--------------------------------------------------------------------------
    |
    | قائمة قواعد البيانات وأعوام القبول المتاحة في النظام مع دعم البادئات التلقائية (test_).
    |
    */
    'databases' => $databases,

    /*
    |--------------------------------------------------------------------------
    | Default Database
    |--------------------------------------------------------------------------
    |
    | قاعدة البيانات الافتراضية عند بدء الجلسة.
    |
    */
    'default_database' => $defaultDb,

    'prefix' => $prefix,
];
