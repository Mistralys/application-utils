@echo off

cls 

call php -d upload_max_filesize=6M -d post_max_size=6M vendor/phpunit/phpunit/phpunit %*

echo.
echo.

pause