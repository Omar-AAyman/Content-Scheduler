# Content Scheduler

Hey there! Content Scheduler is a slick Laravel app that makes it easy to create, schedule, and publish posts across multiple platforms. Built with Laravel 10, PHP 8.1, MySQL, and a clean Bootstrap frontend, it’s got everything you need for managing content like a pro. Think secure logins, post scheduling, analytics, and automated publishing all wrapped in clean, well-tested code that shows off solid software craftsmanship.


## What It Does

- **User Accounts**: Sign up, log in, and manage your profile securely with Laravel Sanctum. Every action (like logging in or out) is tracked for transparency.
- **Post Management**: Create, edit, schedule, reschedule, or delete posts, and choose which platforms to publish to. Posts have statuses like draft or published.
- **Platform Support**: Pick active platforms (e.g., Twitter, Instagram) for your posts, with automatic tracking of what’s active or not.
- **Analytics Dashboard**: See how your posts are doing with stats on platforms, statuses, and time-based trends (week, month, year).
- **Auto-Publishing**: A `posts:publish` command handles scheduled posts, updating their status and logging what happens.
- **Activity Tracking**: Every major action (post creation, platform changes) is logged for easy auditing.
- **Testing**: Solid PHPUnit tests cover post creation, platform toggling, and publishing, ensuring everything works as expected.

## Requirements

- PHP 8.1 or higher
- Composer (latest)
- MySQL 8.0+
- Laravel 10.10+
- Optional: Redis/Memcached for caching

## Database Setup

The app uses migrations to set up these tables:

- **Users**: Stores your name, email, and password.
- **Posts**: Holds post details like title, content, image, scheduled time, and status.
- **Platforms**: Lists platforms (e.g., name, type, max content length).
- **Post_Platform**: Connects posts to platforms, tracking publish status (pending, published, etc.).
- **User_Platform**: Links users to platforms, noting which are active.
- **Activity_Logs**: Keeps a record of actions like creating or deleting posts.

The `TestDatabaseSeeder` adds sample users, platforms, and posts for testing. To set it up:

```bash
php artisan migrate --seed
```

## How to Get Started

1. **Clone the Repo**

   ```bash
   git clone https://github.com/Omar-AAyman/Content-Scheduler.git
   cd Content-Scheduler
   ```

2. **Install Dependencies**

   ```bash
   composer install
   ```

3. **Set Up Environment**

   Copy the example `.env` file and add your database details:

   ```bash
   cp .env.example .env
   ```

   Update `.env`:

   ```
   APP_NAME=ContentScheduler
   APP_URL=http://localhost
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=content_scheduler
   DB_USERNAME=root
   DB_PASSWORD=admin
   ```

   Optional: Add Redis/Memcached for caching if you want.

4. **Generate App Key**

   ```bash
   php artisan key:generate
   ```

5. **Run Migrations and Seeders**

   ```bash
   php artisan migrate --seed
   ```

6. **Start the App**

   ```bash
   php artisan serve
   ```

   Open `http://localhost:8000` in your browser.

7. **Set Up Auto-Publishing**

   Test the publishing command:

   ```bash
   php artisan posts:publish
   ```

   For production, uncomment this line in `app/Console/Kernel.php`:

   ```php
   $schedule->command('posts:publish')->everyMinute();
   ```

   Then run the scheduler:

   ```bash
   php artisan schedule:run
   ```

8. **Run Queue Worker**

   For background tasks:

   ```bash
   php artisan queue:work
   ```

## How It’s Built and Why

### How It’s Built

- **Structure**: The app uses Laravel’s MVC setup with controllers for authentication, posts, platforms, profiles, and analytics. Form requests (like `StorePostRequest`) keep validation clean and reusable.
- **Authentication**: Laravel Sanctum handles secure logins. Events and a `UserObserver` log actions like sign-ins for security.
- **Posts**: The `PostController` manages all post actions, with a `PostObserver` tracking changes. Posts link to platforms via a pivot table.
- **Auto-Publishing**: The `posts:publish` command checks for due posts, updates statuses, and logs results. It’s ready for real platform APIs.
- **Analytics**: The `AnalyticsController` uses cached queries to show post stats efficiently, covering platforms and time ranges.
- **Platforms**: The `PlatformController` lets you toggle platforms, with a `PlatformObserver` logging changes. Caching speeds things up.
- **Events**: Custom events (like `PostPublished`) and listeners ensure reliable logging, set up in `EventServiceProvider`.
- **Frontend**: Bootstrap, JavaScript, and CSS create a clean, responsive UI. It’s lightweight but looks great on any device.
- **Testing**: Tests in `tests/Feature` cover post management, platform toggling, and publishing, using `TestDatabaseSeeder` for reliable setups.

### Trade-offs

- **Caching vs. Fresh Data**: Analytics data is cached for 15 minutes to speed up the dashboard, but it might be slightly out-of-date. WebSockets could make it real-time.
- **Synchronous Publishing**: The publishing command runs synchronously for simplicity but is queue-ready for production scalability.
- **Simple Frontend**: Bootstrap and minimal JavaScript keep things fast and easy to build. A framework like Vue.js could add interactivity but would take more time.
- **Database**: The normalized schema (with pivot tables) is flexible but makes some queries complex. Denormalizing could speed up analytics but complicate updates.
- **Error Handling**: The publishing command logs errors well but simulates API calls. Real APIs would need extra handling for rate limits or retries.
- **Tests**: Core features are tested, but edge cases (like simultaneous publishing) aren’t covered to save time. More tests could improve reliability.

## Video Demo

A video showing off the app (login, post scheduling, platform toggling, analytics, and publishing) is available here: https://drive.google.com/drive/folders/1OyO67KbQMjP4IGpTpXDnDJOxXPBfaLJP?usp=drive_link

## Extra Commands

Clear caches:

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

Run tests:

```bash
php artisan test
```

Check logs:

```bash
tail -f storage/logs/laravel.log
```

## Notes for Developers

- **Code Quality**: Laravel Pint (`./vendor/bin/pint`) keeps the code tidy and follows PSR-12 standards.
- **Future Features**: Easy to add new platforms, analytics, or notifications (e.g., via email or Pusher).
- **Performance**: Caching and query optimizations (like `DB::raw`) keep things fast. Indexes on `posts.scheduled_time` and `user_id` help, too.
- **Security**: Password hashing, CSRF protection, and authorization are in place. Could add rate limiting or two-factor auth later.

This project is all about clean, reliable code with a user-friendly vibe, built to make content scheduling a breeze. 🚀
