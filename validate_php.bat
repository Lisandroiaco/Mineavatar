@echo off
cd /d "C:\Users\lisii\OneDrive\Escritorio\Proyecto Discord"
echo Validating PHP files in root directory...
for /r %%f in (*.php) do @php -l "%%f"
echo.
echo Validating PHP files in includes directory...
cd includes
for /r %%f in (*.php) do @php -l "%%f"
