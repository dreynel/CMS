@echo off
setlocal enabledelayedexpansion
title Raeden's Resto, Bakery, Cafe - Setup & Database Migration
chcp 65001 >nul
cd /d "%~dp0"

echo ================================================================================
echo        RAEDEN'S RESTO, BAKERY, CAFE - 1-CLICK XAMPP SETUP ^& MIGRATION
echo ================================================================================
echo Target Environment: Local XAMPP (root with NO password)
echo Project Folder:     %~dp0
echo.

:: -----------------------------------------------------------------------------
:: 1. LOCATE XAMPP EXECUTABLES (MYSQL.EXE & PHP.EXE)
:: -----------------------------------------------------------------------------
set "MYSQL_EXE="
set "PHP_EXE="

:: Check relative parent path (standard XAMPP structure: <xampp>\htdocs\CMS)
for %%i in ("%~dp0..\..") do set "PARENT_XAMPP=%%~fi"
if exist "!PARENT_XAMPP!\mysql\bin\mysql.exe" set "MYSQL_EXE=!PARENT_XAMPP!\mysql\bin\mysql.exe"
if exist "!PARENT_XAMPP!\php\php.exe" set "PHP_EXE=!PARENT_XAMPP!\php\php.exe"

:: Check common drive paths if not detected in parent
if "!MYSQL_EXE!"=="" (
    if exist "C:\xampp\mysql\bin\mysql.exe" set "MYSQL_EXE=C:\xampp\mysql\bin\mysql.exe"
    if exist "D:\xampp\mysql\bin\mysql.exe" set "MYSQL_EXE=D:\xampp\mysql\bin\mysql.exe"
    if exist "E:\xampp\mysql\bin\mysql.exe" set "MYSQL_EXE=E:\xampp\mysql\bin\mysql.exe"
)
if "!PHP_EXE!"=="" (
    if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
    if exist "D:\xampp\php\php.exe" set "PHP_EXE=D:\xampp\php\php.exe"
    if exist "E:\xampp\php\php.exe" set "PHP_EXE=E:\xampp\php\php.exe"
)

:: Check Windows PATH
if "!MYSQL_EXE!"=="" (
    for %%X in (mysql.exe) do (
        set "ITEM=%%~$PATH:X"
        if defined ITEM set "MYSQL_EXE=!ITEM!"
    )
)
if "!PHP_EXE!"=="" (
    for %%X in (php.exe) do (
        set "ITEM=%%~$PATH:X"
        if defined ITEM set "PHP_EXE=!ITEM!"
    )
)

:: Interactive fallback prompt if still not found
if "!MYSQL_EXE!"=="" (
    echo [!] Could not automatically locate mysql.exe in standard paths.
    set /p "USER_XAMPP=Please enter your XAMPP installation directory (e.g., C:\xampp): "
    if exist "!USER_XAMPP!\mysql\bin\mysql.exe" set "MYSQL_EXE=!USER_XAMPP!\mysql\bin\mysql.exe"
    if exist "!USER_XAMPP!\php\php.exe" set "PHP_EXE=!USER_XAMPP!\php\php.exe"
)

if not exist "!MYSQL_EXE!" (
    echo.
    echo [ERROR] mysql.exe was not found.
    echo Please make sure XAMPP is installed and that the CMS folder is inside htdocs.
    echo.
    pause
    exit /b 1
)

echo [OK] Detected MySQL: "!MYSQL_EXE!"
if defined PHP_EXE echo [OK] Detected PHP:   "!PHP_EXE!"
echo.

:: -----------------------------------------------------------------------------
:: 2. VERIFY MYSQL SERVICE CONNECTIVITY & AUTHENTICATION
:: -----------------------------------------------------------------------------
echo [*] Checking MySQL service connectivity on localhost:3306...
set "MYSQL_AUTH=-u root"
set "DB_PASS_VAL="

:TEST_CONNECTION
"!MYSQL_EXE!" !MYSQL_AUTH! --connect-timeout=2 -e "SELECT 1;" >nul 2>&1
if %errorlevel% neq 0 (
    :: Check if access was denied due to password
    for /f "delims=" %%E in ('"!MYSQL_EXE!" !MYSQL_AUTH! -e "SELECT 1;" 2^>^&1') do set "ERR_LINE=%%E"
    echo !ERR_LINE! | findstr /i "1045" >nul
    if !errorlevel! equ 0 (
        echo [!] Root user requires a password on this computer.
        set /p "DB_PASS_VAL=Please enter MySQL root password (leave blank if none): "
        if "!DB_PASS_VAL!"=="" (
            set "MYSQL_AUTH=-u root"
        ) else (
            set "MYSQL_AUTH=-u root -p!DB_PASS_VAL!"
        )
        goto TEST_CONNECTION
    )

    echo.
    echo [!] Cannot connect to MySQL. XAMPP MySQL service is either STOPPED or blocked.
    echo.
    echo     ACTION REQUIRED:
    echo     1. Open your XAMPP Control Panel.
    echo     2. Click 'Start' next to MySQL (and Apache).
    echo.
    echo Press any key to retry connection test...
    pause >nul
    goto TEST_CONNECTION
)

if "!DB_PASS_VAL!"=="" (
    echo [OK] MySQL is active and responding (connected as user 'root' with NO password).
) else (
    echo [OK] MySQL is active and responding (connected as user 'root' with password).
)
echo.

:: -----------------------------------------------------------------------------
:: 3. CONFIGURE LOCAL DATABASE CREDENTIALS (config/db_credentials.php)
:: -----------------------------------------------------------------------------
echo [*] Configuring database credentials for local XAMPP...
if not exist "%~dp0config" mkdir "%~dp0config"

(
echo ^<?php
echo /**
echo  * Localhost XAMPP Database Credentials
echo  * Generated automatically by setup.bat
echo  */
echo if ^(!defined^('DB_HOST'^)^) define^('DB_HOST', 'localhost'^);
echo if ^(!defined^('DB_PORT'^)^) define^('DB_PORT', '3306'^);
echo if ^(!defined^('DB_NAME'^)^) define^('DB_NAME', 'u534933225_cms'^);
echo if ^(!defined^('DB_USER'^)^) define^('DB_USER', 'root'^);
echo if ^(!defined^('DB_PASS'^)^) define^('DB_PASS', '!DB_PASS_VAL!'^);
) > "%~dp0config\db_credentials.php"

echo [OK] Saved credentials to config\db_credentials.php.
echo.

:: -----------------------------------------------------------------------------
:: 4. CREATE DATABASE (u534933225_cms)
:: -----------------------------------------------------------------------------
echo [*] Creating database 'u534933225_cms' if it does not exist...
"!MYSQL_EXE!" !MYSQL_AUTH! -e "CREATE DATABASE IF NOT EXISTS \`u534933225_cms\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if %errorlevel% neq 0 (
    echo [ERROR] Failed to create database 'u534933225_cms'.
    pause
    exit /b 1
)
echo [OK] Database 'u534933225_cms' is ready.
echo.

:: -----------------------------------------------------------------------------
:: 5. IMPORT SCHEMA & SEED DATA
:: -----------------------------------------------------------------------------
echo [*] Importing database schema and seed data...
set "SQL_FILE=%~dp0database.sql"
if not exist "!SQL_FILE!" set "SQL_FILE=%~dp0database_hostinger.sql"

if not exist "!SQL_FILE!" (
    echo [ERROR] No SQL dump file (database.sql) found in %~dp0!
    pause
    exit /b 1
)

echo     Importing from: "!SQL_FILE!"...
"!MYSQL_EXE!" !MYSQL_AUTH! --default-character-set=utf8mb4 u534933225_cms < "!SQL_FILE!"
if %errorlevel% neq 0 (
    echo [WARNING] Import reported non-zero code. Verifying imported tables...
) else (
    echo [OK] SQL schema imported successfully!
)
echo.

:: -----------------------------------------------------------------------------
:: 6. VERIFY DATABASE POPULATION
:: -----------------------------------------------------------------------------
echo [*] Verifying table records in 'u534933225_cms'...
"!MYSQL_EXE!" !MYSQL_AUTH! -t u534933225_cms -e "SELECT 'Users' AS \`Entity\`, COUNT(*) AS \`Record Count\` FROM users UNION ALL SELECT 'Products', COUNT(*) FROM products UNION ALL SELECT 'Dining Tables', COUNT(*) FROM dining_tables UNION ALL SELECT 'Categories', COUNT(*) FROM categories;"
echo.

:: -----------------------------------------------------------------------------
:: 7. VERIFY DIRECTORIES & ASSETS
:: -----------------------------------------------------------------------------
if not exist "%~dp0assets\images\products" (
    mkdir "%~dp0assets\images\products"
    echo [OK] Created assets\images\products directory.
)

echo ================================================================================
echo                       SETUP COMPLETED SUCCESSFULLY!
echo ================================================================================
echo.
echo Application URL: http://localhost/CMS/
echo.
echo Default User Credentials:
echo   - Administrator : admin   / admin123  (Full control, inventory, yield, reports)
echo   - Cashier       : cashier / cashier123 (Touch POS, checkout, discounts, receipt)
echo   - Barista       : barista / barista123 (Kitchen Display System / KDS queue)
echo   - Staff         : staff   / staff123   (Front desk seating check-in & QR scan)
echo.
echo Customer Online Booking: http://localhost/CMS/booking
echo ================================================================================
echo.

set /p "LAUNCH_NOW=Would you like to launch the system in your default browser now? (Y/N) [default=Y]: "
if /i "!LAUNCH_NOW!"=="" set "LAUNCH_NOW=Y"
if /i "!LAUNCH_NOW!"=="Y" (
    start http://localhost/CMS/
)

echo.
echo You can run 'run.bat' anytime to open the system stations.
echo Press any key to finish...
pause >nul
exit /b 0
