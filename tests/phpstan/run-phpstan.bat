@echo off

cls 

set AnalysisLevel=7
set OutputFile=./result.txt
set ConfigFile=./phpstan.neon
set BinFolder=../../vendor/bin

echo -------------------------------------------------------
echo RUNNING PHPSTAN @ LEVEL %AnalysisLevel%
echo -------------------------------------------------------

echo.

call %BinFolder%/phpstan analyse -c %ConfigFile% -l %AnalysisLevel% > %OutputFile%

start "" "%OutputFile%"
