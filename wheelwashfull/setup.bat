@echo off
echo ====================================
echo WashMate OTP Login System Setup
echo ====================================
echo.

echo Step 1: Installing Laravel Sanctum...
call composer require laravel/sanctum
if %errorlevel% neq 0 (
    echo Error: Failed to install Sanctum
    pause
    exit /b 1
)
echo.

echo Step 2: Running migrations...
call php artisan migrate
if %errorlevel% neq 0 (
    echo Error: Failed to run migrations
    pause
    exit /b 1
)
echo.

echo Step 3: Seeding test users...
call php artisan db:seed --class=UserSeeder
if %errorlevel% neq 0 (
    echo Warning: Failed to seed users (optional)
)
echo.

echo Step 4: Clearing cache...
call php artisan config:clear
call php artisan cache:clear
call php artisan route:clear
echo.

echo ====================================
echo Setup Complete!
echo ====================================
echo.
echo Test Users Created:
echo - Customer: 1111111111
echo - Partner:  2222222222
echo - Admin:    3333333333
echo.
echo Next Steps:
echo 1. Start the server: php artisan serve
echo 2. Test API at: http://localhost:8000/api
echo 3. Import Postman collection: WashMate_API.postman_collection.json
echo 4. Read documentation: API_DOCUMENTATION.md
echo.
pause
