# Admin Panel Template

## If You are going to add some Feature and or you are fixing a bug inside then you need to push inside panel another please Done push code inside a repo

## Steps

1. First Clone the Project

2. Install php version 7.2.34

3. Migrate database you can use this command

```
php artisan migrate:fresh --seed
```

4. Run this command also for installing all dependency

```
composer install
```

5. Run the command below to serve the app

```
php artisan serve
```

6. Make sure add logo and other info inside site setting

8)To enable xDebug, do the following:

-   Inside index.php, add:

```
phpinfo();
exit;
```

-   Serve the app via `php artisan serve`

-   Copy the PhpInfo text and paste into `https://xdebug.org/wizard`

-   Follow the instructions provided to setup debugger.

Theme demo
Need any change you can check theme
https://themesbrand.com/skote/layouts/index.html

Other Layouts
https://preview.themeforest.net/item/skote-html-laravel-admin-dashboard-template/full_screen_preview/25548061?_ga=2.238064128.1839665091.1628076782-147820294.1628076782
