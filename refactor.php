<?php
$filePath = 'app/Filament/Resources/Applicants/Schemas/ApplicantForm.php';
$content = file_get_contents($filePath);

// We want to extract the arrays and put them at the top of configure method.
// Let's just do it directly.

$highSchoolRegex = '/(TextInput::make\(\'SEC_SCHOOL_YEAR\'\).*?)(?=\s*Fieldset::make\(\'بيانات المتقدم\'\))/s';
preg_match($highSchoolRegex, $content, $matches);
$highSchoolFields = "[\n" . $matches[1] . "\n]";

$personalRegex = '/(TextInput::make\(\'FIRST_NAME\'\).*?)(?=\s*\\\\Filament\\\\Forms\\\\Components\\\\Repeater::make\(\'clearing_attachments_list\'\))/s';
preg_match($personalRegex, $content, $matches);
$personalFields = "[\n" . rtrim($matches[1], " \t\n\r,") . "\n]";

$attachmentsRegex = '/(\\\\Filament\\\\Forms\\\\Components\\\\Repeater::make\(\'clearing_attachments_list\'\).*?->columnSpanFull\(\),)/s';
preg_match($attachmentsRegex, $content, $matches);
$attachmentsFields = "[\n" . $matches[1] . "\n]";

$admissionRegex = '/(function\s*\(\w+\s*\$get\)\s*\{\s*if\s*\(\$get\(\'is_not_found\'\)\).*?return\s*\\\\App\\\\Filament\\\\Schemas\\\\CoordinationSchema::getSchema\(\);\s*\})/s';
preg_match($admissionRegex, $content, $matches);
$admissionFields = $matches[1];

// Now we replace the wizard with dynamic rendering
// The wizard part
$wizardRegex = '/Wizard::make\(\[\s*Step::make\(\'البحث والتحقق\'\).*?\]\)\s*->columnSpan\(\'full\'\),/s';
preg_match($wizardRegex, $content, $wizardMatch);
$originalWizard = $wizardMatch[0];

// In the original Wizard, the schemas were:
// Step 2 (بيانات الطالب) -> schema([ Callouts..., Fieldset(HS)->schema($hs), Fieldset(Personal)->schema($personal), Repeater... ])
// Step 3 -> schema($admission)

// We replace those explicit arrays in originalWizard with our variables so the Wizard is smaller.
$newWizard = preg_replace($highSchoolRegex, '...$highSchoolFields, ', $originalWizard);
$newWizard = preg_replace($personalRegex, '...$personalFields, ', $newWizard);
$newWizard = preg_replace($attachmentsRegex, '...$attachmentsFields, ', $newWizard);
$newWizard = preg_replace($admissionRegex, '$admissionFields', $newWizard);

// Now we construct the replacement code
$replacement = <<<PHP
\$isEdit = \$schema->getLivewire() instanceof \\Filament\\Resources\\Pages\\EditRecord;

\$highSchoolFields = {$highSchoolFields};
\$personalFields = {$personalFields};
\$attachmentsFields = {$attachmentsFields};
\$admissionFields = {$admissionFields};

\$mainComponent = \$isEdit ? \\Filament\\Schemas\\Components\\Tabs::make('Tabs')->tabs([
    \\Filament\\Schemas\\Components\\Tabs\\Tab::make('بيانات شخصية')
        ->icon('heroicon-o-user')
        ->schema(\$personalFields)->columns(4),
    \\Filament\\Schemas\\Components\\Tabs\\Tab::make('بيانات الثانوية')
        ->icon('heroicon-o-academic-cap')
        ->schema(\$highSchoolFields)->columns(4),
    \\Filament\\Schemas\\Components\\Tabs\\Tab::make('بيانات المقاصة والقبول')
        ->icon('heroicon-o-document-check')
        ->schema(\$admissionFields)->columns(2),
    \\Filament\\Schemas\\Components\\Tabs\\Tab::make('المرفقات')
        ->icon('heroicon-o-paper-clip')
        ->schema(\$attachmentsFields),
])->columnSpanFull() : {$newWizard};

return \$schema
    ->components([
        Grid::make(12)
            ->schema([
                // ... we need to replace the content inside components([ Grid... ])
PHP;

// Wait, doing this via regex might be brittle.
// I will just construct the new file programmatically or output to a file so I can review it.

file_put_contents('refactored.txt', $replacement);
echo "Done";
