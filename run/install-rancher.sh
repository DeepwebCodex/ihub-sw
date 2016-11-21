#!/usr/bin/env bash

echo "--- Preparing config.."
chmod -R 777 ./storage && chmod -R 777 ./bootstrap/cache
cp ./.env.example ./.env

sed -i -e "s/^APP_URL=http:\/\/localhost/APP_URL=http:\/\/ihub.favbet.dev/g" \
       -e "s/^APP_ENV=local/APP_ENV=local/g" \
       -e "s/^DB_HOST=127.0.0.1/DB_HOST=postgresql/g" \
       -e "s/^DB_USERNAME=pgsql/DB_USERNAME=postgres/g" \
       -e "s/^DB_PASSWORD=pgsq/DB_PASSWORD=mysecretpassword/g" \
       -e "s/^CACHE_DRIVER=file/CACHE_DRIVER=redis/g" \
       -e "s/^SESSION_DRIVER=file/SESSION_DRIVER=redis/g" \
       -e "s/^REDIS_HOST=127.0.0.1/REDIS_HOST=redis/g" \
       -e "s/^REDIS_PORT=6379/REDIS_PORT=6379/g" \
       -e "s/^LOG_DRIVER=file/LOG_DRIVER=rabbit/g" \
       -e "s/^LOG_MONGO_SERVER=/LOG_MONGO_SERVER=mongodb:\/\/mongo:27017/g" \
       -e "s/^LOG_MONGO_DB_NAME=/LOG_MONGO_DB_NAME=logs/g" \
       -e "s/^LOG_MONGO_COLLECTION_NAME=/LOG_MONGO_COLLECTION_NAME=integration/g" \
       -e "s/^LOG_RABBIT_HOST=/LOG_RABBIT_HOST=rabbitmq-server/g" \
       -e "s/^LOG_RABBIT_PORT=/LOG_RABBIT_PORT=5672/g" \
       -e "s/^LOG_RABBIT_USER=/LOG_RABBIT_USER=ihub/g" \
       -e "s/^LOG_RABBIT_PASS=/LOG_RABBIT_PASS=ihub/g" \
       -e "s/^STATSD_HOST=localhost/STATSD_HOST=statsd/g" \
       ./.env

cp ./.env ./.env.testing

sed -i -e "s/^APP_ENV=local/APP_ENV=testing/g" \
       ./.env.testing

echo "--- Installing vendors.."
composer install