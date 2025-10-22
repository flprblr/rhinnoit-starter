# Local Installation

```bash
composer clear-cache &&
composer install &&
composer pint &&
npm cache verify &&
npm install &&
npm run format &&
rm -rf public/storage &&
php artisan storage:link &&
php artisan config:clear &&
php artisan event:clear &&
php artisan route:clear &&
php artisan view:clear &&
php artisan about
```

# Daily Update

```bash
composer update &&
npm update &&
composer pint &&
npm run format
```

# Prepare to Production   

```bash
composer clear-cache &&
composer install &&
composer pint &&
npm cache verify &&
npm install &&
npm run format &&
npm run build &&
rm -rf node_modules &&
rm -rf public/storage &&
rm -rf storage/logs/laravel.log
cd .. &&
zip -q -r laravel.zip laravel -x ".DS_Store" -x "__MACOSX"
```

## Production Deployment

```bash
unzip -qo laravel.zip &&
rm -f laravel.zip &&
rm -rf public_html &&
ln -s /home/smapp/laravel/public /home/smapp/public_html &&
cd laravel &&
find /home/smapp/laravel/ -name ".DS_Store" -type f -delete &&
rm -rf public/storage &&
composer clear-cache &&
composer install &&
php artisan storage:link &&
php artisan event:clear &&
php artisan route:clear &&
php artisan view:clear &&
php artisan config:clear &&
php artisan event:cache &&
php artisan route:cache &&
php artisan view:cache &&
composer install --optimize-autoloader --no-dev &&
php artisan config:cache &&
php artisan about
```