# News Aggregation Platform

This is a Laravel-based news aggregation platform that fetches articles from various sources and displays them in a modern, responsive interface using Vue.js and Inertia.js.

## Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/your-username/news-aggregation.git
   cd news-aggregation
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies:**
   ```bash
   npm install
   # or
   pnpm install
   ```

4. **Set up your environment file:**
   ```bash
   cp .env.example .env
   ```

5. **Generate an application key:**
   ```bash
   php artisan key:generate
   ```

6. **Configure your `.env` file:**
   Update the database credentials and any other necessary settings in the `.env` file.

7. **Run database migrations:**
   ```bash
   php artisan migrate
   ```

## Running the Application

To start the development server, you can use the following command. This will concurrently run the PHP server, the Vite development server, and the queue listener.

```bash
composer run dev
```

Alternatively, you can run the components separately:

- **Start the Laravel development server:**
  ```bash
  php artisan serve
  ```
- **Start the Vite development server:**
  ```bash
  npm run dev
  ```
- **Start the queue listener:**
  ```bash
  php artisan queue:listen
  ```

## Running Tests

To run the test suite, use the following command:

```bash
composer test
```

This will execute the Pest test suite.

## Scheduler

The application includes a scheduled task to sync articles from the configured news sources. The `articles:sync` command is scheduled to run every minute.

To run the scheduler locally, you need to set up a cron job that executes the following command every minute:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Alternatively, you can manually run the sync command:

```bash
php artisan articles:sync
```

When you run the command manually, you will be prompted to select the news sources you want to sync.
