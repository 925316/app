我们从0开始搭建一个网站，我想用laravel12+vue+mysql，
至于写网站什么类型先不管，我完全不懂前端，但我需要熟悉这个流程。我想先学会改这里面的登录界面等样式。
我们知道官方示例流程会用starter kit中的vue,但为了基础，肯定要自己composer require laravel/breeze手动用这个脚手架

[user@localhost wwwroot]$ laravel new app
 ┌ Which starter kit would you like to install? ────────────────┐
 │ None                                                         │
 ┌ Which testing framework do you prefer? ──────────────────────┐
 │ PHPUnit                                                      │
 ┌ Do you want to install Laravel Boost to improve AI assisted coding? ┐
 │ No                                                                  │
 Creating a "laravel/laravel" project at "./app"
Installing laravel/laravel (v12.10.1)
  - Downloading laravel/laravel (v12.10.1)
  - Installing laravel/laravel (v12.10.1): Extracting archive
Created project in /www/wwwroot/app
....
> @php -r "file_exists('.env') || copy('.env.example', '.env');"
   INFO  Application key set successfully.
 ┌ Which database will your application use? ───────────────────┐
 │ MySQL                                                        │
 ┌ Default database updated. Would you like to run the default database migrations? ┐
 │ Yes                                                                              │

   INFO  Preparing database.
  Creating migration table .............................................................................. 17.64ms DONE
   INFO  Running migrations.

  0001_01_01_000000_create_users_table .................................................................. 14.96ms DONE
  0001_01_01_000001_create_cache_table ................................................................... 3.19ms DONE
  0001_01_01_000002_create_jobs_table .................................................................... 9.75ms DONE

 ┌ Would you like to run npm install and npm run build? ────────┐
 │ Yes                                                          │
 added 82 packages in 9s

21 packages are looking for funding
  run `npm fund` for details

> build
> vite build

vite v7.2.6 building client environment for production...
✓ 53 modules transformed.
public/build/manifest.json             0.33 kB │ gzip:  0.17 kB
public/build/assets/app-B-uLOrqM.css  33.81 kB │ gzip:  8.48 kB
public/build/assets/app-CAiCLEjY.js   36.35 kB │ gzip: 14.71 kB
✓ built in 335ms
   INFO  Application ready in [app]. You can start your local development using:
➜ cd app
➜ composer run dev

[user@localhost wwwroot]$

目前我不用vite部署，而是nginx指向public目录和伪静态。目前我可以正常访问改网址了。
我们应该先安装认证脚手架

所以
[user@localhost wwwroot]$ cd app/
[user@localhost app]$ composer require laravel/breeze --dev
./composer.json has been updated
Running composer update laravel/breeze
Loading composer repositories with package information
Updating dependencies
Lock file operations: 1 install, 0 updates, 0 removals
  - Locking laravel/breeze (v2.3.8)
Writing lock file
Installing dependencies from lock file (including require-dev)
Package operations: 1 install, 0 updates, 0 removals
  - Installing laravel/breeze (v2.3.8): Extracting archive
Generating optimized autoload files
> Illuminate\Foundation\ComposerScripts::postAutoloadDump
> @php artisan package:discover --ansi

   INFO  Discovering packages.

  laravel/breeze ................................................................................................ DONE
  laravel/pail .................................................................................................. DONE
  laravel/sail .................................................................................................. DONE
  laravel/tinker ................................................................................................ DONE
  nesbot/carbon ................................................................................................. DONE
  nunomaduro/collision .......................................................................................... DONE
  nunomaduro/termwind ........................................................................................... DONE

81 packages you are using are looking for funding.
Use the `composer fund` command to find out more!
> @php artisan vendor:publish --tag=laravel-assets --ansi --force

   INFO  No publishable resources for tag [laravel-assets].

No security vulnerability advisories found.
Using version ^2.3 for laravel/breeze
[user@localhost app]$ php artisan breeze:install vue
./composer.json has been updated
Running composer update inertiajs/inertia-laravel laravel/sanctum tightenco/ziggy
Loading composer repositories with package information
Updating dependencies
Lock file operations: 3 installs, 0 updates, 0 removals
  - Locking inertiajs/inertia-laravel (v2.0.11)
  - Locking laravel/sanctum (v4.2.1)
  - Locking tightenco/ziggy (v2.6.0)
Writing lock file
Installing dependencies from lock file (including require-dev)
Package operations: 3 installs, 0 updates, 0 removals
    0 [>---------------------------]    0 [->--------------------------]
  - Installing inertiajs/inertia-laravel (v2.0.11): Extracting archive
  - Installing laravel/sanctum (v4.2.1): Extracting archive
  - Installing tightenco/ziggy (v2.6.0): Extracting archive
 0/3 [>---------------------------]   0%
 3/3 [============================] 100%
Generating optimized autoload files
> Illuminate\Foundation\ComposerScripts::postAutoloadDump
> @php artisan package:discover --ansi

   INFO  Discovering packages.

  inertiajs/inertia-laravel ............................................. DONE
  laravel/breeze ........................................................ DONE
  laravel/pail .......................................................... DONE
  laravel/sail .......................................................... DONE
  laravel/sanctum ....................................................... DONE
  laravel/tinker ........................................................ DONE
  nesbot/carbon ......................................................... DONE
  nunomaduro/collision .................................................. DONE
  nunomaduro/termwind ................................................... DONE
  tightenco/ziggy ....................................................... DONE

81 packages you are using are looking for funding.
Use the `composer fund` command to find out more!
> @php artisan vendor:publish --tag=laravel-assets --ansi --force

   INFO  No publishable resources for tag [laravel-assets].

No security vulnerability advisories found.

   INFO  Installing and building Node dependencies.
   INFO  Breeze scaffolding installed successfully.

[user@localhost app]$

现在网站可以正常登录和注册了。