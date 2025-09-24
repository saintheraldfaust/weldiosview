@echo off
echo Opening php.ini file for editing...
echo.
echo INSTRUCTIONS:
echo 1. Find lines 931-932 (around extension=gd)
echo 2. You should see:
echo    extension=gd
echo    extension=php_gd.dll
echo.
echo 3. Comment out ONE of them by adding semicolon at the start:
echo    extension=gd
echo    ;extension=php_gd.dll
echo.
echo 4. Save the file and close notepad
echo 5. Restart Apache in XAMPP Control Panel
echo.
pause
notepad "C:\xampp\php\php.ini"