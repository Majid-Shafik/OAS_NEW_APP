<?php

$db = new PDO('mysql:host=127.0.0.1;dbname=p_oas_db_2022', 'root', '');
$stmt = $db->query('select * from `applicant` order by `applicant`.`APPLICANT_IDENT` asc limit 10 offset 0');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    echo "APPLICANT_IDENT: {$row['APPLICANT_IDENT']}, FULL_NAME: {$row['FULL_NAME']}\n";
}
