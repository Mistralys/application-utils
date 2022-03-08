@echo off

cls 

set AnalysisLevel=6
set OutputFile=phpstan/output.txt
set ConfigFile=./config/phpstan.neon

echo -------------------------------------------------------
echo RUNNING PHPSTAN @ LEVEL %AnalysisLevel%
echo -------------------------------------------------------

echo.

call ../vendor/bin/phpstan analyse -c %ConfigFile% -l %AnalysisLevel% > %OutputFile%

start "" "%OutputFile%"
