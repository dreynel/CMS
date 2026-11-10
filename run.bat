@echo off
setlocal enabledelayedexpansion
title Raeden's Resto, Bakery, Cafe - Launch Control
chcp 65001 >nul
cd /d "%~dp0"

:: -----------------------------------------------------------------------------
:: 1. LOCATE PHP EXECUTABLE (PHP.EXE)
:: -----------------------------------------------------------------------------
set "PHP_EXE="

for %%i in ("%~dp0..\..") do set "PARENT_DIR=%%~fi"
if exist "!PARENT_DIR!\php\php.exe" set "PHP_EXE=!PARENT_DIR!\php\php.exe"

if "!PHP_EXE!"=="" (
    if exist "C:\php\php.exe" set "PHP_EXE=C:\php\php.exe"
    if exist "C:\Program Files\PHP\php.exe" set "PHP_EXE=C:\Program Files\PHP\php.exe"
    if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
    if exist "D:\xampp\php\php.exe" set "PHP_EXE=D:\xampp\php\php.exe"
    if exist "E:\xampp\php\php.exe" set "PHP_EXE=E:\xampp\php\php.exe"
)

if "!PHP_EXE!"=="" (
    for %%X in (php.exe) do (
        set "ITEM=%%~$PATH:X"
        if defined ITEM set "PHP_EXE=!ITEM!"
    )
)

if "!PHP_EXE!"=="" set "PHP_EXE=php"

:: -----------------------------------------------------------------------------
:: 2. DETECT SERVER MODE (PHP BUILT-IN ON 8000 vs APACHE ON 80)
:: -----------------------------------------------------------------------------
set "BASE_URL=http://localhost:8000"

:: Check if port 8000 is already listening
netstat -ano | findstr ":8000" >nul 2>&1
if %errorlevel% equ 0 (
    set "BASE_URL=http://localhost:8000"
    goto OPEN_BROWSER
)

:: Check if Apache port 80 is listening
netstat -ano | findstr ":80 " >nul 2>&1
if %errorlevel% equ 0 (
    set "BASE_URL=http://localhost/CMS"
    goto OPEN_BROWSER
)

:: Neither is running -> Auto-start PHP built-in server on localhost:8000
echo [*] Starting local PHP server on http://localhost:8000/ ...
start "Raedens Cafe Server (Port 8000)" cmd /k "cd /d "%~dp0" && "!PHP_EXE!" -S localhost:8000 router.php"
timeout /t 2 >nul
set "BASE_URL=http://localhost:8000"

:OPEN_BROWSER
:: Auto-launch main application in browser
start %BASE_URL%/

:: -----------------------------------------------------------------------------
:: 3. INTERACTIVE STATION SWITCHER
:: -----------------------------------------------------------------------------
:MENU
cls
echo ================================================================================
echo           RAEDEN'S RESTO, BAKERY, CAFE - SYSTEM LAUNCH CONTROL
echo ================================================================================
echo Project Folder: %~dp0
echo Active Base URL: %BASE_URL%/
echo.
echo Select an operational station to open in your browser:
echo.
echo   [1] Customer Reservation & Menu     --^>  %BASE_URL%/booking
echo   [2] Cashier Point of Sale (POS)     --^>  %BASE_URL%/pos
echo   [3] Kitchen Display System (KDS)    --^>  %BASE_URL%/kitchen
echo   [4] Staff Seating Desk & QR Scanner --^>  %BASE_URL%/scanner
echo   [5] Management Dashboard & Admin    --^>  %BASE_URL%/admin
echo   [6] Customer QR Pass Lookup         --^>  %BASE_URL%/pass
echo   [7] 1-Click Database Reset Tool     --^>  %BASE_URL%/setup
echo   [8] Run Database Setup (setup.bat)
echo   [0] Exit Launch Control
echo.
echo ================================================================================
set "CHOICE="
set /p "CHOICE=Enter station number [1-8, 0 to exit]: "

if "%CHOICE%"=="1" (
    start %BASE_URL%/booking
    goto MENU
)
if "%CHOICE%"=="2" (
    start %BASE_URL%/pos
    goto MENU
)
if "%CHOICE%"=="3" (
    start %BASE_URL%/kitchen
    goto MENU
)
if "%CHOICE%"=="4" (
    start %BASE_URL%/scanner
    goto MENU
)
if "%CHOICE%"=="5" (
    start %BASE_URL%/admin
    goto MENU
)
if "%CHOICE%"=="6" (
    start %BASE_URL%/pass
    goto MENU
)
if "%CHOICE%"=="7" (
    start %BASE_URL%/setup
    goto MENU
)
if "%CHOICE%"=="8" (
    call "%~dp0setup.bat"
    goto MENU
)
if "%CHOICE%"=="0" (
    exit /b 0
)

echo [!] Invalid option. Please enter a number between 0 and 8.
timeout /t 1 >nul
goto MENU
