# Local Installation

```bash
composer install --no-interaction &&
npm install &&
php artisan optimize:clear &&
rm -rf public/storage &&
php artisan storage:link &&
php artisan wayfinder:generate &&
composer pint &&
npm run format &&
php artisan about
```

# Daily Update

```bash
composer update --no-interaction &&
npm update &&
php artisan wayfinder:generate &&
composer pint &&
npm run format
```

# Prepare to Production

**macOS/Linux:**

```bash
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

**Windows:**

```powershell
composer clear-cache; `
composer install --no-interaction --no-dev; `
composer pint; `
npm install; `
npm run format; `
npm run build; `
Remove-Item -Recurse -Force node_modules, public\storage, storage\logs\laravel.log -ErrorAction SilentlyContinue; `
cd ..; `
Compress-Archive -Path laravel -DestinationPath laravel.zip -Force
```

## Production Deployment

```bash
unzip -qo laravel.zip &&
rm -f laravel.zip &&
rm -rf public_html &&
ln -s /home/USER_DIR/laravel/public /home/USER_DIR/public_html &&
cd laravel &&
find /home/USER_DIR/laravel/ -name ".DS_Store" -type f -delete &&
rm -rf public/storage &&
composer clear-cache &&
composer install --no-interaction --optimize-autoloader --no-dev &&
php artisan optimize:clear &&
php artisan storage:link &&
php artisan event:cache &&
php artisan route:cache &&
php artisan view:cache &&
php artisan config:cache &&
php artisan about
```
