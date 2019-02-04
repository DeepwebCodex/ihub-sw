#!/usr/bin/env bash

cp ./.env.example ./.env
sed -i -e "s/^APP_URL=http:\/\/localhost/APP_URL=http:\/\/ihub.favorit/g" \
    -e "s/^APP_ENV=local/APP_ENV=production/g" \
    -e "s/^APP_DEBUG=true/APP_DEBUG=true/g" \
    -e "s/^CACHE_REPOSITORY_ENABLED_CACHE=true/CACHE_REPOSITORY_ENABLED_CACHE=false/g" \
    \
    -e "s/^CACHE_DRIVER=file/CACHE_DRIVER=array/g" \
    -e "s/^MSGPACK_REDIS_DRIVER=msgpack-redis/MSGPACK_REDIS_DRIVER=file/g" \
    \
    -e "s/^DB_HOST=example.com/DB_HOST=postgres/g" \
    -e "s/^DB_PORT=5432/DB_PORT=5432/g" \
    -e "s/^DB_DATABASE=db/DB_DATABASE=ihub/g" \
    -e "s/^DB_USERNAME=pgsql/DB_USERNAME=postgres/g" \
    -e "s/^DB_PASSWORD=pgsql/DB_PASSWORD=\"mysecretpassword\"/g" \
    \
    ./.env

echo "" >> ./.env
