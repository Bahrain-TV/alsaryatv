#!/bin/bash

echo "Running FluxServiceProvider diagnostics..."
php diagnose-flux-provider.php

echo -e "\n\nChecking for PHP startup errors..."
php -r "echo phpinfo();" | grep -i error

echo -e "\n\nChecking syntax of FluxServiceProvider.php..."
php -l app/Flux/FluxServiceProvider.php

echo -e "\n\nDisplaying contents of FluxServiceProvider.php..."
cat app/Flux/FluxServiceProvider.php

echo -e "\n\nChecking permissions of FluxServiceProvider.php..."
ls -l app/Flux/FluxServiceProvider.php

echo -e "\n\nRunning Composer dump-autoload..."
composer dump-autoload -v

echo -e "\n\nChecking Composer autoload files..."
ls -l vendor/composer/

echo -e "\n\nVerifying Flux namespace in autoload_psr4.php..."
grep -n "Flux" vendor/composer/autoload_psr4.php

echo -e "\n\nChecking Composer classmap..."
grep -R "Flux" vendor/composer/autoload_classmap.php

echo -e "\n\nChecking Laravel package discovery..."
php artisan package:discover --verbose

echo -e "\n\nChecking Laravel bootstrap process..."
grep -n "autoload" bootstrap/app.php

echo -e "\n\nListing all service providers..."
grep -R "ServiceProvider" config/app.php

echo -e "\n\nChecking for conflicts in other service providers..."
grep -R "Flux" app/Providers/*.php

echo -e "\n\nVerifying FluxServiceProvider registration in AppServiceProvider..."
grep -n "FluxServiceProvider" app/Providers/AppServiceProvider.php

echo -e "\n\nAttempting to manually load FluxServiceProvider..."
php -r "
require_once 'vendor/autoload.php';
if (class_exists('Flux\\FluxServiceProvider')) {
    echo 'FluxServiceProvider class found and loaded successfully.\n';
} else {
    echo 'FluxServiceProvider class not found. Attempting manual include...\n';
    include 'app/Flux/FluxServiceProvider.php';
    if (class_exists('Flux\\FluxServiceProvider')) {
        echo 'FluxServiceProvider class found after manual include.\n';
    } else {
        echo 'FluxServiceProvider class still not found after manual include.\n';
    }
}"

echo -e "\n\nChecking PHP opcache status..."
php -r "print_r(opcache_get_status());"

echo -e "\n\nVerifying Composer autoloader optimization..."
grep -n "optimized" vendor/composer/autoload_real.php

echo -e "\n\nChecking file permissions in the project directory..."
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

echo -e "\n\nVerifying Laravel config cache..."
php artisan config:clear
php artisan config:cache

echo -e "\n\nChecking for any conflicting class aliases..."
grep -R "class_alias" vendor/

echo -e "\n\nVerifying PHP extension requirements..."
php -r "print_r(get_loaded_extensions());"

echo -e "\n\nChecking for any conflicting namespace declarations..."
grep -R "namespace Flux" app/ vendor/

echo -e "\n\nVerifying Composer lock file..."
composer validate --no-check-all --strict