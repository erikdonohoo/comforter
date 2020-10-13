#!/bin/bash

rm -f /var/run/httpd/httpd.pid

cd /www/web

if [ ! -f .env ]; then
  cp .env.example .env
fi

/usr/bin/php /usr/local/bin/composer install --no-ansi --no-interaction --no-progress --no-scripts --optimize-autoloader

exec /usr/sbin/httpd -D FOREGROUND
