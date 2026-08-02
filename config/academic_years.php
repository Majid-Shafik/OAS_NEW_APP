<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Academic Years & Database Connections Configuration
|--------------------------------------------------------------------------
|
| يمكنك تحديد إعدادات قواعد البيانات الخاصة بأعوام التنسيق بعدة طرق:
|
| 1. استخدام بادئة (ACADEMIC_DB_PREFIX):
|    - محلياً (Local): ACADEMIC_DB_PREFIX= (أو تركها فارغة) -> ينتج p_oas_db_2022, p_oas_db_2021 ...
|    - في السيرفر التجريبي (Test): ACADEMIC_DB_PREFIX=test_ -> ينتج test_p_oas_db_2022, test_p_oas_db_2021 ...
|
| 2. أو تحديد القائمة كاملة يدوياً عبر (ACADEMIC_DATABASES):
|    ACADEMIC_DATABASES="test_p_oas_db_2022:2022-2021,test_p_oas_db_2021:2021-2020"
|    أو بصيغة JSON:
|    ACADEMIC_DATABASES='{"test_p_oas_db_2022":"2022-2021","test_p_oas_db_2021":"2021-2020"}'
|
| 3. تحديد القاعدة الافتراضية عند فتح صفحة الدخول (DEFAULT_ACADEMIC_DATABASE):
|    DEFAULT_ACADEMIC_DATABASE=p_oas_db_2022
|
*/

$dbPrefix = (string) env('ACADEMIC_DB_PREFIX', '');
$rawDatabases = env('ACADEMIC_DATABASES');

$databases = [];

if (!empty($rawDatabases)) {
    // التحقق إذا كانت القيمة ممررة كـ JSON
    $json = json_decode((string) $rawDatabases, true);
    if (is_array($json)) {
        $databases = $json;
    } else {
        // إذا كانت القيمة مفصولة بفواصل "db1:label1,db2:label2"
        $pairs = explode(',', (string) $rawDatabases);
        foreach ($pairs as $pair) {
            $parts = explode(':', trim($pair));
            if (count($parts) >= 2) {
                $databases[trim($parts[0])] = trim($parts[1]);
            } elseif (count($parts) === 1 && !empty($parts[0])) {
                $db = trim($parts[0]);
                if (preg_match('/(20\d{2})/', $db, $m)) {
                    $y = (int) $m[1];
                    $databases[$db] = "{$y}-" . ($y - 1);
                } else {
                    $databases[$db] = $db;
                }
            }
        }
    }
}

// القائمة الافتراضية في حال عدم تمرير قائمة مخصصة
if (empty($databases)) {
    $baseDatabases = [
        'p_oas_db_2022' => '2022-2021',
        'p_oas_db_2021' => '2021-2020',
        'p_oas_db_2020' => '2020-2019',
        'p_oas_db_2019' => '2019-2018',
        'p_oas_db_2018' => '2018-2017',
    ];

    if ($dbPrefix !== '') {
        foreach ($baseDatabases as $baseDb => $label) {
            $databases[$dbPrefix . $baseDb] = $label;
        }
    } else {
        $databases = $baseDatabases;
    }
}

$firstDbKey = !empty($databases) ? array_key_first($databases) : 'p_oas_db_2022';
$defaultDatabase = env('DEFAULT_ACADEMIC_DATABASE', env('DB_DATABASE', $firstDbKey));

return [
    'prefix' => $dbPrefix,
    'databases' => $databases,
    'default_database' => $defaultDatabase,
];
