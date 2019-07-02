#!/usr/bin/env bash

cp ./.env.example ./.env
sed -i -e "s/^APP_ENV=null/APP_ENV=production/g" \
    -e "s/^APP_DEBUG=null/APP_DEBUG=true/g" \
    \
    -e "s/^CACHE_DRIVER=null/CACHE_DRIVER=array/g" \
    \
    -e "s/^DB_HOST=example.com/DB_HOST=postgres/g" \
    -e "s/^DB_PORT=6666/DB_PORT=5432/g" \
    -e "s/^DB_DATABASE=db/DB_DATABASE=ihub/g" \
    -e "s/^DB_USERNAME=pgsql/DB_USERNAME=postgres/g" \
    -e "s/^DB_PASSWORD=pgsql/DB_PASSWORD=\"mysecretpassword\"/g" \
    \
    ./.env

echo "" >> ./.env
