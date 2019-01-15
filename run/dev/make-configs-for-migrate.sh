#!/usr/bin/env bash

cp ./.env.example ./.env
sed -i -e "s/^APP_URL=http:\/\/localhost/APP_URL=http:\/\/ihub.favbet.devel/g" \
    -e "s/^APP_ENV=local/APP_ENV=dev/g" \
    \
    -e "s/^APP_DEBUG=true/APP_DEBUG=true/g" \
    -e "s/^CACHE_REPOSITORY_ENABLED_CACHE=true/CACHE_REPOSITORY_ENABLED_CACHE=false/g" \
    -e "s/^APP_LOG_LEVEL=debug/APP_LOG_LEVEL=debug/g" \
    \
    -e "s/^APP_REQUEST_DEBUG=true/APP_REQUEST_DEBUG=true/g" \
    -e "s/^LOG_EXTERNAL_REQUESTS=false/LOG_EXTERNAL_REQUESTS=true/g" \
    \
    -e "s/^SESSION_DRIVER=file/SESSION_DRIVER=file/g" \
    -e "s/^CACHE_DRIVER=file/CACHE_DRIVER=array/g" \
    -e "s/^MSGPACK_REDIS_DRIVER=msgpack-redis/MSGPACK_REDIS_DRIVER=file/g" \
    -e "s/^LOG_DRIVER=file/LOG_DRIVER=file/g" \
    -e "s/^BROADCAST_DRIVER=log/BROADCAST_DRIVER=log/g" \
    -e "s/^QUEUE_CONNECTION=sync/QUEUE_CONNECTION=sync/g" \
    \
    -e "s/^DB_HOST=example.com/DB_HOST=de2db02d.dev.favorit/g" \
    -e "s/^DB_PORT=5432/DB_PORT=6432/g" \
    -e "s/^DB_DATABASE=db/DB_DATABASE=ihub/g" \
    -e "s/^DB_USERNAME=pgsql/DB_USERNAME=u_ihub/g" \
    -e "s/^DB_PASSWORD=pgsql/DB_PASSWORD=\"b9c3q46-9bv08967\"/g" \
    \
    ./.env