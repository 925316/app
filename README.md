## **Clone and enter the project:**
```bash
git clone [repo](/)
cd app
```

## **Install PHP dependencies:**
```bash
composer install
```

## **Install frontend dependencies and start the dev build process (for development):**
```bash
npm install && npm run build
```

## **Configure environment:**
Copy `.env.example` to `.env` and configure your database settings:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```
Then generate the application key:
```bash
php artisan key:generate
```

## **Run database migrations (and seed initial data):**
```bash
php artisan migrate:fresh --seed
```

## **Start the local development server:**
```bash
composer run dev
```
Your application will be available at `localhost:8000`.

## (Optional) Run tests:
```bash
php artisan test
```

## clean up
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
composer dump-autoload
```