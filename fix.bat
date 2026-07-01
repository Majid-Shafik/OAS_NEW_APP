@echo off
echo Installing Filament without strict version constraint...
call composer require filament/filament -W
call php artisan filament:install --panels
echo Setup completed. Please let me know when you are done.
