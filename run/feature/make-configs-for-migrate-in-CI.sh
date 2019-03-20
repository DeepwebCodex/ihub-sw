#!/usr/bin/env bash

cp ./.env.example ./.env
sed -i -e "s/^APP_ENV=null/APP_ENV=dev/g" \
    \
    -e "s/^APP_DEBUG=null/APP_DEBUG=true/g" \
    -e "s/^APP_LOG_LEVEL=null/APP_LOG_LEVEL=debug/g" \
    \
    -e "s/^APP_REQUEST_DEBUG=null/APP_REQUEST_DEBUG=true/g" \
    -e "s/^LOG_EXTERNAL_REQUESTS=null/LOG_EXTERNAL_REQUESTS=true/g" \
    \
    -e "s/^SESSION_DRIVER=null/SESSION_DRIVER=file/g" \
    -e "s/^CACHE_DRIVER=null/CACHE_DRIVER=array/g" \
    -e "s/^LOG_DRIVER=null/LOG_DRIVER=file/g" \
    -e "s/^BROADCAST_DRIVER=null/BROADCAST_DRIVER=log/g" \
    -e "s/^QUEUE_CONNECTION=null/QUEUE_CONNECTION=sync/g" \
    \
    -e "s/^DB_HOST=example.com/DB_HOST=postgres/g" \
    -e "s/^DB_PORT=6666/DB_PORT=5432/g" \
    -e "s/^DB_DATABASE=db/DB_DATABASE=ihub/g" \
    -e "s/^DB_USERNAME=pgsql/DB_USERNAME=postgres/g" \
    -e "s/^DB_PASSWORD=pgsql/DB_PASSWORD=\"mysecretpassword\"/g" \
    \
    ./.env