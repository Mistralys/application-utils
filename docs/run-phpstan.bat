@echo off

cls 

echo -------------------------------------------------------
echo RUNNING PHPSTAN ANALYSIS
echo -------------------------------------------------------

echo.

call ../vendor/bin/phpstan analyse -c ./config/phpstan.neon -l 5 > phpstan/output.txt

start "" "phpstan/output.txt" 
