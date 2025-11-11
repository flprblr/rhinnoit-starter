# Requisitos Previos

- PHP >= 8.1
- Composer
- Node.js y npm
- Git

# Local Installation

```bash
set -e
composer install --no-interaction &&
npm install &&
php artisan config:clear &&
php artisan event:clear &&
php artisan route:clear &&
rm -rf public/storage &&
php artisan storage:link &&
php artisan wayfinder:generate &&
php artisan view:clear &&
composer pint &&
npm run format &&
php artisan about
```

# Daily Update

```bash
set -e
composer update --no-interaction &&
npm update &&
php artisan config:clear &&
php artisan event:clear &&
php artisan route:clear &&
php artisan wayfinder:generate &&
php artisan view:clear &&
composer pint &&
npm run format &&
php artisan about
```

# Prepare to Production

```bash
set -e
composer clear-cache &&
composer install --no-interaction --no-dev &&
composer pint &&
npm install &&
npm run format &&
npm run build &&
rm -rf node_modules public/storage storage/logs/laravel.log &&
cd .. &&
zip -q -r laravel.zip laravel -x ".DS_Store" -x "__MACOSX"
```

## Production Deployment

> **Note:** Replace `USER_DIR` with your server username.

```bash
set -e
unzip -qo laravel.zip &&
rm -f laravel.zip &&
rm -rf public_html &&
ln -s /home/USER_DIR/laravel/public /home/USER_DIR/public_html &&
cd laravel &&
find /home/USER_DIR/laravel/ -name ".DS_Store" -type f -delete &&
rm -rf public/storage &&
composer install --no-interaction --optimize-autoloader --no-dev &&
php artisan config:clear &&
php artisan event:clear &&
php artisan route:clear &&
php artisan view:clear &&
php artisan storage:link &&
php artisan event:cache &&
php artisan route:cache &&
php artisan view:cache &&
php artisan config:cache &&
php artisan about
```
