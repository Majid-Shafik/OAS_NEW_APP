<?php
$file = __DIR__.'/../app/Filament/Resources/Applicants/Schemas/ApplicantEditForm.php';
$content = file_get_contents($file);

$content = str_replace('class ApplicantForm', 'class ApplicantEditForm', $content);

// Find High School
$startHS = strpos($content, "TextInput::make('SEC_SCHOOL_YEAR')");
$endHS = strpos($content, "Fieldset::make('بيانات المتقدم')", $startHS);
$endHS = strrpos(substr($content, 0, $endHS), "],") - 1; // find the closing bracket of schema array
$highSchoolFields = substr($content, $startHS, $endHS - $startHS);
// Adjust to capture secondary_certificate too
$endHS2 = strpos($content, "Fieldset::make('بيانات المتقدم')", $startHS);
$endHS2 = strrpos(substr($content, 0, $endHS2), "])->columns(4),");
$highSchoolFields = substr($content, $startHS, $endHS2 - $startHS);

// Find Personal
$startPers = strpos($content, "TextInput::make('FIRST_NAME')", $endHS2);
$endPers = strpos($content, "\Filament\Forms\Components\Repeater::make('clearing_attachments_list')", $startPers);
$endPers = strrpos(substr($content, 0, $endPers), "])\n                                            ->columns(4)") ?: strrpos(substr($content, 0, $endPers), "])\n");
$personalFields = substr($content, $startPers, $endPers - $startPers);

// Find Attachments
$startAtt = strpos($content, "\Filament\Forms\Components\Repeater::make('clearing_attachments_list')", $endPers);
$endAtt = strpos($content, "Step::make('بيانات المقاصة والقبول')", $startAtt);
$endAtt = strrpos(substr($content, 0, $endAtt), "])->columns(3),");
$attachmentsFields = substr($content, $startAtt, $endAtt - $startAtt);

// Find Admission
$startAdm = strpos($content, "function (Get \$get) {", $endAtt);
$endAdm = strpos($content, "})", $startAdm) + 2;
$admissionFields = substr($content, $startAdm, $endAdm - $startAdm);

$replacement = <<<PHP
                            \Filament\Schemas\Components\Tabs::make('Tabs')
                                ->tabs([
                                    \Filament\Schemas\Components\Tabs\Tab::make('بيانات شخصية')
                                        ->icon('heroicon-o-user')
                                        ->schema([
                                            {$personalFields}
                                        ])->columns(4),
                                    \Filament\Schemas\Components\Tabs\Tab::make('بيانات الثانوية')
                                        ->icon('heroicon-o-academic-cap')
                                        ->schema([
                                            {$highSchoolFields}
                                        ])->columns(4),
                                    \Filament\Schemas\Components\Tabs\Tab::make('بيانات المقاصة والقبول')
                                        ->icon('heroicon-o-document-check')
                                        ->schema({$admissionFields})->columns(2),
                                    \Filament\Schemas\Components\Tabs\Tab::make('المرفقات')
                                        ->icon('heroicon-o-paper-clip')
                                        ->schema([
                                            {$attachmentsFields}
                                        ]),
                                ])
                                ->columnSpanFull(),
PHP;

$startWizard = strpos($content, "Wizard::make([");
$endWizard = strpos($content, "->columnSpan('full'),", $startWizard) + strlen("->columnSpan('full'),");
$content = substr_replace($content, $replacement, $startWizard, $endWizard - $startWizard);

file_put_contents($file, $content);
echo "Successfully refactored ApplicantEditForm.php";
