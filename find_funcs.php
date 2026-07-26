<?php
$content = file_get_contents('C:\laragon\www\oas\archive2\p22\un_staff_22\include\functions.php');
if (preg_match_all('/function\s+(check_[a-zA-Z0-9_]*|ADD_NEW_APPLICATIONS)\s*\(/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
    foreach ($matches[1] as $match) {
        $name = $match[0];
        $offset = $match[1];
        // find end of function (simple approximation)
        $end = strpos($content, "\nfunction ", $offset);
        if ($end === false) $end = $offset + 2000;
        echo "========================================\n";
        echo substr($content, $offset - 10, $end - $offset + 10) . "\n";
    }
}
