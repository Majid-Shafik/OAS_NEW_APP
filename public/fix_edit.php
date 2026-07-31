<?php

$source = __DIR__.'/../app/Filament/Resources/Applicants/Schemas/ApplicantForm.php';
$target = __DIR__.'/../app/Filament/Resources/Applicants/Schemas/ApplicantEditForm.php';

$content = file_get_contents($source);
$content = str_replace('class ApplicantForm', 'class ApplicantEditForm', $content);

// The goal is to replace Wizard with Tabs.
// Let's find the start of Wizard
$wizardStart = strpos($content, "Wizard::make([");

// Find Step 1
$step1Start = strpos($content, "Step::make('البحث والتحقق')", $wizardStart);
// Find Step 2
$step2Start = strpos($content, "Step::make('بيانات الطالب')", $step1Start);

// We delete Step 1 entirely.
// We also delete Callouts in Step 2.
$fieldset1Start = strpos($content, "Fieldset::make('بيانات الثانوية')", $step2Start);

// Extract the High School fieldset
$fieldset2Start = strpos($content, "Fieldset::make('بيانات المتقدم')", $fieldset1Start);
$highSchoolCode = substr($content, $fieldset1Start, $fieldset2Start - $fieldset1Start);

// Extract Personal fieldset
$repeaterStart = strpos($content, "\\Filament\\Forms\\Components\\Repeater::make('clearing_attachments_list')", $fieldset2Start);
$personalCode = substr($content, $fieldset2Start, $repeaterStart - $fieldset2Start);

// Extract Repeater
$step3Start = strpos($content, "Step::make('بيانات المقاصة والقبول')", $repeaterStart);
$repeaterCode = substr($content, $repeaterStart, $step3Start - $repeaterStart);
// Trim the trailing "])->columns(3)," from the end of Step 2
$repeaterCode = preg_replace('/\]\)->columns\(3\),\s*$/', '', $repeaterCode);

// Extract Step 3 schema (Admission)
// It looks like: ->schema(function (Get $get) { ... })->columns(2),
$schemaStart = strpos($content, "->schema(function", $step3Start);
$wizardEnd = strpos($content, "])\n                                ->columnSpan('full'),", $schemaStart);
if ($wizardEnd === false) {
    $wizardEnd = strpos($content, "])\n                                ->columnSpan('full')", $schemaStart);
}
$admissionCode = substr($content, $schemaStart, $wizardEnd - $schemaStart);
// Remove trailing closing bracket of Step 3 if it's there
$admissionCode = rtrim(trim($admissionCode), ',');

// Now build the Tabs code
$tabsCode = <<<PHP
\Filament\Schemas\Components\Tabs::make('Tabs')
    ->tabs([
        \Filament\Schemas\Components\Tabs\Tab::make('بيانات شخصية')
            ->icon('heroicon-o-user')
            ->schema([
                {$personalCode}
            ]),
        \Filament\Schemas\Components\Tabs\Tab::make('بيانات الثانوية')
            ->icon('heroicon-o-academic-cap')
            ->schema([
                {$highSchoolCode}
            ]),
        \Filament\Schemas\Components\Tabs\Tab::make('بيانات المقاصة والقبول')
            ->icon('heroicon-o-document-check')
            {$admissionCode},
        \Filament\Schemas\Components\Tabs\Tab::make('المرفقات')
            ->icon('heroicon-o-paper-clip')
            ->schema([
                {$repeaterCode}
            ]),
    ])
    ->columnSpan('full'),
PHP;

// Replace the Wizard block with Tabs block
$newContent = substr_replace($content, $tabsCode, $wizardStart, ($wizardEnd + strlen("])\n                                ->columnSpan('full'),")) - $wizardStart);

file_put_contents($target, $newContent);

// Check syntax
exec("php -l " . escapeshellarg($target), $output, $returnVar);
if ($returnVar === 0) {
    echo "Success: " . implode("\n", $output);
} else {
    echo "Syntax Error:\n" . implode("\n", $output);
}
