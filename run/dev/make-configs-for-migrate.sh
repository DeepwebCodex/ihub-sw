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
    \
    -e "s/^DB_HOST=example.com/DB_HOST=de2db02d.dev.favorit/g" \
    -e "s/^DB_PORT=6666/DB_PORT=6432/g" \
    -e "s/^DB_DATABASE=db/DB_DATABASE=ihub/g" \
    -e "s/^DB_USERNAME=pgsql/DB_USERNAME=u_ihub/g" \
    -e "s/^DB_PASSWORD=pgsql/DB_PASSWORD=\"b9c3q46-9bv08967\"/g" \
    \
    ./.env