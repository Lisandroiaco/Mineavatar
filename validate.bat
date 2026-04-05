@echo off
cd /d "C:\Users\lisii\OneDrive\Escritorio\Proyecto Discord"
echo === PHP Syntax Validation ===
echo.
echo [1/4] Validating includes/data.php
php -l includes/data.php
echo.
echo [2/4] Validating index.php
php -l index.php
echo.
echo [3/4] Validating includes/repository.php
php -l includes/repository.php
echo.
echo [4/4] Validating includes/layout.php
php -l includes/layout.php
echo.
echo === Validation Complete ===
