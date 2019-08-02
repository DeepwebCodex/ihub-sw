#!/usr/bin/env bash

cp ./.env.example ./.env
sed -i -e "s/^APP_ENV=null/APP_ENV=production/g" \
    -e "s/^APP_DEBUG=null/APP_DEBUG=true/g" \
    \
    -e "s/^CACHE_DRIVER=null/CACHE_DRIVER=array/g" \
    \
    ./.env

echo "" >> ./.env
cat /root/ihub/env.icms >> .env
