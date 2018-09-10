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
    ./.env

echo "" >> ./.env
cat /root/ihub/env.icms >> .env
