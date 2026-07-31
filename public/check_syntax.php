<?php
exec("php -l ../app/Filament/Resources/Applicants/Schemas/ApplicantEditForm.php", $output, $returnVar);
echo implode("\n", $output);
