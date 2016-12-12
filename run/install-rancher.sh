#!/usr/bin/env bash

echo "--- Preparing config.."
chmod -R 777 ./storage && chmod -R 777 ./bootstrap/cache
cp ./.env.example ./.env

sed -i -e "s/^APP_URL=http:\/\/localhost/APP_URL=http:\/\/ihub.favbet.dev/g" \
       -e "s/^APP_ENV=local/APP_ENV=local/g" \
       -e "s/^DB_HOST=127.0.0.1/DB_HOST=dbsmall.intsrv/g" \
       -e "s/^DB_USERNAME=pgsql/DB_USERNAME=u_ihub/g" \
       -e "s/^DB_PASSWORD=pgsql/DB_PASSWORD=b9c3q46-9bv08967​/g" \
       -e "s/^CACHE_DRIVER=file/CACHE_DRIVER=redis/g" \
       -e "s/^SESSION_DRIVER=file/SESSION_DRIVER=redis/g" \
       -e "s/^REDIS_HOST=127.0.0.1/REDIS_HOST=redispub.intsrv/g" \
       -e "s/^REDIS_PORT=6379/REDIS_PORT=6379/g" \
       -e "s/^LOG_DRIVER=file/LOG_DRIVER=rabbit/g" \
       -e "s/^LOG_RABBIT_HOST=/LOG_RABBIT_HOST=rabbitmq-server/g" \
       -e "s/^LOG_RABBIT_PORT=/LOG_RABBIT_PORT=5672/g" \
       -e "s/^LOG_RABBIT_USER=/LOG_RABBIT_USER=ihub/g" \
       -e "s/^LOG_RABBIT_PASS=/LOG_RABBIT_PASS=ihub/g" \
       -e "s/^STATSD_HOST=localhost/STATSD_HOST=statsd/g" \
       ./.env

cp ./.env ./.env.testing

sed -i -e "s/^APP_ENV=local/APP_ENV=testing/g" \
       -e "s/^SESSION_DRIVER=redis/SESSION_DRIVER=redis/g" \
       -e "s/^LOG_DRIVER=rabbit/LOG_DRIVER=file/g" \
       -e "s/^REDIS_HOST=redis/REDIS_HOST=redispub.intsrv/g" \
       -e "s/^REDIS_PORT=6379/REDIS_PORT=6379/g" \
       ./.env.testing

echo "" >> ./.env.testing
echo "TEST_USER_ID=89" >> ./.env.testing
echo "TEST_PARTNER_ID=1" >> ./.env.testing
echo "TEST_CASHEDESK=-5" >> ./.env.testing

echo "--- Installing composer.."
echo ">> composer install"
composer install

echo "--- Running migrate.."
echo ">> php artisan migrate --database=ihub"
php artisan migrate --database=ihub
