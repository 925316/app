<p align="center"><a href="https://inertiajs.com/" target="_blank"><img src="https://raw.githubusercontent.com/inertiajs/inertia/master/.github/LOGO.png" width="520"></a></p>
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>
<p align="center"><a href="https://www.mysql.com" target="_blank"><img src="https://www.mysql.com/common/logos/logo-mysql-170x115.png" width="150"></a></p>
<p align="center"><a href="https://nginx.org" target="_blank"><img src="https://nginx.org/img/nginx_logo_dark.svg" width="230"></a></p>
<p align="center"><a href="https://nodejs.org" target="_blank"><img src="https://nodejs.org/static/images/logo.svg" width="230"></a></p>
<p align="center"><a href="https://www.php.net" target="_blank"><img src="https://www.php.net/images/logos/new-php-logo.svg" width="200"></a></p>
<p align="center"><a href="https://tailwindcss.com" target="_blank"><img src="https://tailwindcss.com/_next/static/media/tailwindcss-logotype-white.830c8e49.svg" width="400"></a></p>
<p align="center"><a href="https://vuejs.org" target="_blank"><img src="https://vuejs.org/logo.svg" width="120"></a></p>

## 🛠️ technology stack selection

| Level         | Technology           | Description                                                                |
| ------------- | -------------------- | -------------------------------------------------------------------------- |
| **Front-end** | Vue 3                | Building Modern Responsive User Interfaces                                 |
|               | Inertia.js           | Achieve SPA experience while enjoying server-side routing and verification |
|               | Tailwind CSS         | A practical CSS framework that enables you to quickly build customized UIs |
| **Backend**   | Laravel 12 (PHP 8.2) | A robust PHP Web framework that offers a complete set of features          |
| **Database**  | MySQL 10.6 (MariaDB) | A relational database for storing core business data                       |
| **Build**     | Node 22.21           | front-end build tool, supporting ES6+ syntax                               |
| Deployment    | Nginx 1.28 + PHP-fPM | Web Server and PHP Process Manager                                         |

## 🚀 Installation

```bash
composer global require laravel/installer
laravel new example-app
laravel/framework (v12.42.0)
```

choose: `Vue starter kit` and `Laravel's built-in authentication`, then configure the database connection after everything is ready.

```bash
cd example-app
npm install && npm run dev
npm install && npm run build
```

Configure the database connection in `.env` file.

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=

php artisan migrate --seed
```

```bash
php artisan clear
php artisan test
```