Content Scheduler
This is a Laravel-based application for scheduling and managing content. It provides a foundation for building a content scheduling system with a focus on simplicity and extensibility.
Requirements

PHP: ^8.1
Composer: Latest version
MySQL: 8.0+
Laravel: ^10.10

Installation
1. Clone the Repository
git clone https://github.com/Omar-AAyman/Content-Scheduler.git
cd Content-Scheduler

2. Install Dependencies
composer install

3. Environment Configuration
Copy the example environment file and update the required settings:
cp .env.example .env

Update the .env file with your database credentials:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=content_scheduler
DB_USERNAME=root
DB_PASSWORD=admin

4. Generate Application Key
php artisan key:generate

5. Run Migrations
php artisan migrate

This will create the necessary database tables.
6. Run the Application
php artisan serve

The application will be available at http://localhost:8000.
Additional Commands
To clear and cache configurations:
php artisan config:clear
php artisan cache:clear
php artisan config:cache

To check the Laravel logs for debugging:
tail -f storage/logs/laravel.log

Development Notes

The project uses Laravel Sanctum for API authentication (if applicable).
The default cache driver is set to file, and the session driver is also file.
For queue-based tasks (if implemented), run:php artisan queue:work




This README provides a clear and concise guide to setting up and running the Content Scheduler project. 🚀
