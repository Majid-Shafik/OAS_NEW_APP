@echo off
echo Generating System model and migration...
call php artisan make:model System -m
echo Please let me know when this is done, so I can add the columns to the migration file before we create the Filament Resource.
