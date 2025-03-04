@echo off

cls 

set /p AnalysisLevel=<level.txt
set OutputFile=./result.txt
set ConfigFile=./phpstan.neon
set BinFolder=../../vendor/bin

echo -------------------------------------------------------
echo RUNNING PHPSTAN @ LEVEL %AnalysisLevel%
echo -------------------------------------------------------

echo.

call %BinFolder%/phpstan analyse -c %ConfigFile% -l %AnalysisLevel% > %OutputFile%

start "" "%OutputFile%"
