@echo off
setlocal enabledelayedexpansion
title Raeden's Resto, Bakery, Cafe - Setup & Database Migration
chcp 65001 >nul
cd /d "%~dp0"

echo ================================================================================
echo        RAEDEN'S RESTO, BAKERY, CAFE - 1-CLICK LOCAL SETUP ^& MIGRATION
echo ================================================================================
echo Target Environment: Localhost (Standalone MySQL 8.0 / XAMPP / MariaDB)
echo Project Folder:     %~dp0
echo.

:: -----------------------------------------------------------------------------
:: 1. LOCATE MYSQL EXECUTABLE (MYSQL.EXE)
:: -----------------------------------------------------------------------------
set "MYSQL_EXE="

:: 1a. Check Oracle MySQL Server in Program Files (MySQL 8.0, 8.4, etc.)
if exist "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" set "MYSQL_EXE=C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe"
if "!MYSQL_EXE!"=="" (
    if exist "C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe" set "MYSQL_EXE=C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe"
    if exist "C:\Program Files\MySQL\MySQL Server 8.1\bin\mysql.exe" set "MYSQL_EXE=C:\Program Files\MySQL\MySQL Server 8.1\bin\mysql.exe"
    if exist "C:\Program Files\MySQL\MySQL Server 8.2\bin\mysql.exe" set "MYSQL_EXE=C:\Program Files\MySQL\MySQL Server 8.2\bin\mysql.exe"
    if exist "C:\Program Files\MySQL\MySQL Server 8.3\bin\mysql.exe" set "MYSQL_EXE=C:\Program Files\MySQL\MySQL Server 8.3\bin\mysql.exe"
    if exist "C:\Program Files (x86)\MySQL\MySQL Server 8.0\bin\mysql.exe" set "MYSQL_EXE=C:\Program Files (x86)\MySQL\MySQL Server 8.0\bin\mysql.exe"
    if exist "C:\Program Files\MySQL\bin\mysql.exe" set "MYSQL_EXE=C:\Program Files\MySQL\bin\mysql.exe"
)

:: 1b. Check XAMPP relative parent path (if project is inside htdocs/CMS)
if "!MYSQL_EXE!"=="" (
    for %%i in ("%~dp0..\..") do set "PARENT_DIR=%%~fi"
    if exist "!PARENT_DIR!\mysql\bin\mysql.exe" set "MYSQL_EXE=!PARENT_DIR!\mysql\bin\mysql.exe"
)

:: 1c. Check standard XAMPP drive locations
if "!MYSQL_EXE!"=="" (
    if exist "C:\xampp\mysql\bin\mysql.exe" set "MYSQL_EXE=C:\xampp\mysql\bin\mysql.exe"
    if exist "D:\xampp\mysql\bin\mysql.exe" set "MYSQL_EXE=D:\xampp\mysql\bin\mysql.exe"
    if exist "E:\xampp\mysql\bin\mysql.exe" set "MYSQL_EXE=E:\xampp\mysql\bin\mysql.exe"
)

:: 1d. Check Windows system PATH
if "!MYSQL_EXE!"=="" (
    for %%X in (mysql.exe) do (
        set "ITEM=%%~$PATH:X"
        if defined ITEM set "MYSQL_EXE=!ITEM!"
    )
)

:: 1e. Interactive prompt if still not found
if "!MYSQL_EXE!"=="" (
    echo [!] Could not automatically locate mysql.exe in standard paths.
    echo     Standard paths checked:
    echo       - C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe
    echo       - C:\xampp\mysql\bin\mysql.exe
    echo.
    set /p "USER_MYSQL=Enter your MySQL path or folder (e.g. C:\Program Files\MySQL\MySQL Server 8.0): "
    set "USER_MYSQL=!USER_MYSQL:"=!"
    set "USER_MYSQL=!USER_MYSQL:/=\!"
    
    if exist "!USER_MYSQL!\bin\mysql.exe" set "MYSQL_EXE=!USER_MYSQL!\bin\mysql.exe"
    if exist "!USER_MYSQL!\mysql.exe" set "MYSQL_EXE=!USER_MYSQL!\mysql.exe"
    if exist "!USER_MYSQL!" (
        if /i "!USER_MYSQL:~-9!"=="mysql.exe" set "MYSQL_EXE=!USER_MYSQL!"
    )
)

if not exist "!MYSQL_EXE!" (
    echo.
    echo [ERROR] mysql.exe was not found.
    echo Please verify that MySQL Server 8.0 or XAMPP is installed.
    echo.
    pause
    exit /b 1
)

echo [OK] Detected MySQL: "!MYSQL_EXE!"

:: -----------------------------------------------------------------------------
:: 2. LOCATE PHP EXECUTABLE (PHP.EXE)
:: -----------------------------------------------------------------------------
set "PHP_EXE="

:: 2a. Check relative parent (XAMPP htdocs)
for %%i in ("%~dp0..\..") do set "PARENT_DIR=%%~fi"
if exist "!PARENT_DIR!\php\php.exe" set "PHP_EXE=!PARENT_DIR!\php\php.exe"

:: 2b. Check common drive paths
if "!PHP_EXE!"=="" (
    if exist "C:\php\php.exe" set "PHP_EXE=C:\php\php.exe"
    if exist "C:\Program Files\PHP\php.exe" set "PHP_EXE=C:\Program Files\PHP\php.exe"
    if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
    if exist "D:\xampp\php\php.exe" set "PHP_EXE=D:\xampp\php\php.exe"
    if exist "E:\xampp\php\php.exe" set "PHP_EXE=E:\xampp\php\php.exe"
)

:: 2c. Check Windows system PATH
if "!PHP_EXE!"=="" (
    for %%X in (php.exe) do (
        set "ITEM=%%~$PATH:X"
        if defined ITEM set "PHP_EXE=!ITEM!"
    )
)

if defined PHP_EXE (
    echo [OK] Detected PHP:   "!PHP_EXE!"
) else (
    echo [NOTE] php.exe not found in standard paths; will rely on system PATH when running.
)
echo.

:: -----------------------------------------------------------------------------
:: 3. VERIFY MYSQL CONNECTIVITY & AUTHENTICATION (PORT 3306)
:: -----------------------------------------------------------------------------
echo [*] Testing MySQL connection on 127.0.0.1:3306...
set "MYSQL_AUTH=-u root -h 127.0.0.1 -P 3306"
set "DB_PASS_VAL="

:TEST_CONNECTION
"!MYSQL_EXE!" !MYSQL_AUTH! --connect-timeout=2 -e "SELECT 1;" >nul 2>&1
if %errorlevel% neq 0 (
    :: Check if failure was 1045 Access Denied (password required)
    for /f "delims=" %%E in ('"!MYSQL_EXE!" !MYSQL_AUTH! -e "SELECT 1;" 2^>^&1') do set "ERR_LINE=%%E"
    echo !ERR_LINE! | findstr /i "1045" >nul
    if !errorlevel! equ 0 (
        echo [!] MySQL root user requires a password.
        set /p "DB_PASS_VAL=Enter your MySQL root password (press Enter if blank): "
        if "!DB_PASS_VAL!"=="" (
            set "MYSQL_AUTH=-u root -h 127.0.0.1 -P 3306"
        ) else (
            set "MYSQL_AUTH=-u root -p!DB_PASS_VAL! -h 127.0.0.1 -P 3306"
        )
        goto TEST_CONNECTION
    )

    echo.
    echo [!] Cannot connect to MySQL on 127.0.0.1:3306.
    echo.
    echo     TROUBLESHOOTING STEPS:
    echo     1. If using MySQL Server 8.0: Open Windows Services (services.msc) and ensure 'MySQL80' is RUNNING.
    echo     2. If using XAMPP: Open XAMPP Control Panel and click 'Start' next to MySQL.
    echo.
    echo Press any key to retry connection test...
    pause >nul
    goto TEST_CONNECTION
)

if "!DB_PASS_VAL!"=="" (
    echo [OK] Successfully connected to MySQL as root (no password).
) else (
    echo [OK] Successfully connected to MySQL as root with password.
)
echo.

:: -----------------------------------------------------------------------------
:: 4. GENERATE LOCAL DATABASE CREDENTIALS FILE (config/db_credentials.php)
:: -----------------------------------------------------------------------------
echo [*] Saving local database credentials to config\db_credentials.php...
if not exist "%~dp0config" mkdir "%~dp0config"

(
echo ^<?php
echo /**
echo  * Local Database Credentials
echo  * Generated automatically by setup.bat
echo  */
echo if ^(!defined^('DB_HOST'^)^) define^('DB_HOST', '127.0.0.1'^);
echo if ^(!defined^('DB_PORT'^)^) define^('DB_PORT', '3306'^);
echo if ^(!defined^('DB_NAME'^)^) define^('DB_NAME', 'u534933225_cms'^);
echo if ^(!defined^('DB_USER'^)^) define^('DB_USER', 'root'^);
echo if ^(!defined^('DB_PASS'^)^) define^('DB_PASS', '!DB_PASS_VAL!'^);
) > "%~dp0config\db_credentials.php"

echo [OK] Saved credentials to config\db_credentials.php.
echo.

:: -----------------------------------------------------------------------------
:: 5. CREATE DATABASE (u534933225_cms)
:: -----------------------------------------------------------------------------
echo [*] Creating database 'u534933225_cms' if it does not exist...
"!MYSQL_EXE!" !MYSQL_AUTH! -e "CREATE DATABASE IF NOT EXISTS \`u534933225_cms\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if %errorlevel% neq 0 (
    echo [ERROR] Failed to create database 'u534933225_cms'.
    pause
    exit /b 1
)
echo [OK] Database 'u534933225_cms' verified.
echo.

:: -----------------------------------------------------------------------------
:: 6. IMPORT DATABASE SCHEMA & SEED DATA
:: -----------------------------------------------------------------------------
echo [*] Importing database tables and initial seed data...
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
    echo [OK] Database schema imported successfully!
)
echo.

:: -----------------------------------------------------------------------------
:: 7. VERIFY DATABASE POPULATION
:: -----------------------------------------------------------------------------
echo [*] Verifying table records in 'u534933225_cms'...
"!MYSQL_EXE!" !MYSQL_AUTH! -t u534933225_cms -e "SELECT 'Users' AS \`Entity\`, COUNT(*) AS \`Record Count\` FROM users UNION ALL SELECT 'Products', COUNT(*) FROM products UNION ALL SELECT 'Dining Tables', COUNT(*) FROM dining_tables UNION ALL SELECT 'Categories', COUNT(*) FROM categories UNION ALL SELECT 'Inventory Items', COUNT(*) FROM inventory_items;"
echo.

:: -----------------------------------------------------------------------------
:: 8. VERIFY DIRECTORIES & ASSETS
:: -----------------------------------------------------------------------------
if not exist "%~dp0assets\images\products" (
    mkdir "%~dp0assets\images\products"
    echo [OK] Created assets\images\products directory.
)

echo ================================================================================
echo                       SETUP COMPLETED SUCCESSFULLY!
echo ================================================================================
echo.
echo Application Local URL: http://localhost:8000/
echo (Or http://localhost/CMS/ if running under XAMPP Apache)
echo.
echo Default User Credentials:
echo   - Administrator : admin   / admin123  (Full control, inventory, yield, reports)
echo   - Cashier       : cashier / cashier123 (Touch POS, checkout, discounts, receipt)
echo   - Barista       : barista / barista123 (Kitchen Display System / KDS queue)
echo   - Staff         : staff   / staff123   (Front desk seating check-in & QR scan)
echo.
echo Customer Online Booking: http://localhost:8000/booking
echo ================================================================================
echo.

set /p "LAUNCH_NOW=Start local server now (php -S localhost:8000 router.php)? (Y/N) [default=Y]: "
if /i "!LAUNCH_NOW!"=="" set "LAUNCH_NOW=Y"
if /i "!LAUNCH_NOW!"=="Y" (
    echo.
    echo Starting PHP Built-in Server on http://localhost:8000/ ...
    echo Press Ctrl+C in this console window anytime to stop the server.
    echo.
    start http://localhost:8000/
    if defined PHP_EXE (
        "!PHP_EXE!" -S localhost:8000 router.php
    ) else (
        php -S localhost:8000 router.php
    )
)

echo.
echo You can run 'run.bat' anytime to start or navigate the system.
echo Press any key to finish...
pause >nul
exit /b 0
