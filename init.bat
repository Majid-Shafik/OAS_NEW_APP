@echo off
echo Creating Laravel project in tmp folder...
call composer create-project laravel/laravel tmp
echo Moving files to current directory...
xcopy tmp\* . /s /e /y
xcopy tmp\.* . /s /e /y
rmdir /s /q tmp
echo Installing Filament...
call composer require filament/filament:"^3.2" -W
call php artisan filament:install --panels
echo Setup completed. Please let me know when you are done.
