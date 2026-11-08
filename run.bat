@echo off
setlocal enabledelayedexpansion
title Raeden's Resto, Bakery, Cafe - Launch Control
chcp 65001 >nul
cd /d "%~dp0"

:: -----------------------------------------------------------------------------
:: 1. AUTO-LAUNCH DEFAULT APPLICATION URL
:: -----------------------------------------------------------------------------
start http://localhost/CMS/

:: -----------------------------------------------------------------------------
:: 2. INTERACTIVE STATION SWITCHER
:: -----------------------------------------------------------------------------
:MENU
cls
echo ================================================================================
echo           RAEDEN'S RESTO, BAKERY, CAFE - SYSTEM LAUNCH CONTROL
echo ================================================================================
echo Project Folder: %~dp0
echo Base Web URL:   http://localhost/CMS/
echo MySQL Target:   localhost:3306 (user: root, no password)
echo.
echo Select an operational station to open in your browser:
echo.
echo   [1] Customer Reservation & Menu     --^>  http://localhost/CMS/booking
echo   [2] Cashier Point of Sale (POS)     --^>  http://localhost/CMS/pos.php
echo   [3] Kitchen Display System (KDS)    --^>  http://localhost/CMS/kitchen.php
echo   [4] Staff Seating Desk & QR Scanner --^>  http://localhost/CMS/scanner.php
echo   [5] Management Dashboard & Admin    --^>  http://localhost/CMS/admin.php
echo   [6] Customer QR Pass Lookup         --^>  http://localhost/CMS/pass.php
echo   [7] 1-Click Database Reset Tool     --^>  http://localhost/CMS/setup.php
echo   [8] Run Database Setup (setup.bat)
echo   [0] Exit Launch Control
echo.
echo ================================================================================
set "CHOICE="
set /p "CHOICE=Enter station number [1-8, 0 to exit]: "

if "%CHOICE%"=="1" (
    start http://localhost/CMS/booking
    goto MENU
)
if "%CHOICE%"=="2" (
    start http://localhost/CMS/pos.php
    goto MENU
)
if "%CHOICE%"=="3" (
    start http://localhost/CMS/kitchen.php
    goto MENU
)
if "%CHOICE%"=="4" (
    start http://localhost/CMS/scanner.php
    goto MENU
)
if "%CHOICE%"=="5" (
    start http://localhost/CMS/admin.php
    goto MENU
)
if "%CHOICE%"=="6" (
    start http://localhost/CMS/pass.php
    goto MENU
)
if "%CHOICE%"=="7" (
    start http://localhost/CMS/setup.php
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
