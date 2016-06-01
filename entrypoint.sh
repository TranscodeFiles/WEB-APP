#!/bin/bash
set -e

# Change www-data's uid & guid to be the same as directory in host
# Fix cache problems
usermod -u `stat -c %u /var/www/html` www-data || true
groupmod -g `stat -c %g /var/www/html` www-data || true

if [ "$1" = "init" ]; then
    composer install
    npm install
    bower install --allow-root
    gulp sass
    php app/console a:i --symlink
    php app/console a:d
    php app/console c:c
    rm -rf /var/www/html/app/cache/* /var/www/html/app/logs/*
    chmod -R 775 /var/www/html
    chown -R www-data:www-data /var/www/html
    su - www-data -c "php /var/wwww/html/app/console d:d:c"
    su - www-data -c "php /var/wwww/html/app/console d:s:u -f"
else
    chmod -R 775 /var/www/html
    chown -R www-data:www-data /var/www/html
    /usr/sbin/apache2 -D FOREGROUND
fi

