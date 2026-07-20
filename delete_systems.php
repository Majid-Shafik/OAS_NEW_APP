<?php
function rmdir_recursive($dir) {
    if (!is_dir($dir)) return;
    foreach(scandir($dir) as $file) {
        if ('.' === $file || '..' === $file) continue;
        if (is_dir("$dir/$file")) rmdir_recursive("$dir/$file");
        else unlink("$dir/$file");
    }
    rmdir($dir);
}
rmdir_recursive('app/Filament/Resources/Systems');
echo "Deleted";
