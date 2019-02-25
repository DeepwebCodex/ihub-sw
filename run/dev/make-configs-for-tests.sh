#!/usr/bin/env bash

cp ./.env.example ./.env.testing
sed -i -e "s/^APP_ENV=null/APP_ENV=testing/g" \
    -e "s/^APP_KEY=example/APP_KEY=\"base64:axrMo7RS1BV9f589cGtb+iQejqRmQdeI071MMMIleE4=\"/g" \
    -e "s/^APP_DEBUG=null/APP_DEBUG=true/g" \
    -e "s/^APP_LOG_LEVEL=null/APP_LOG_LEVEL=warning/g" \
    -e "s/^APP_REQUEST_DEBUG=null/APP_REQUEST_DEBUG=true/g" \
    -e "s/^LOG_EXTERNAL_REQUESTS=null/LOG_EXTERNAL_REQUESTS=true/g" \
    \
    -e "s/^SESSION_DRIVER=null/SESSION_DRIVER=file/g" \
    -e "s/^CACHE_DRIVER=null/CACHE_DRIVER=file/g" \
    -e "s/^LOG_DRIVER=null/LOG_DRIVER=file/g" \
    -e "s/^BROADCAST_DRIVER=null/BROADCAST_DRIVER=log/g" \
    -e "s/^QUEUE_CONNECTION=null/QUEUE_CONNECTION=sync/g" \
    \
    -e "s/^DB_HOST=example.com/DB_HOST=de2db02d.dev.favorit/g" \
    -e "s/^DB_PORT=6666/DB_PORT=6432/g" \
    -e "s/^DB_DATABASE=db/DB_DATABASE=ihub/g" \
    -e "s/^DB_USERNAME=pgsql/DB_USERNAME=u_ihub/g" \
    -e "s/^DB_PASSWORD=pgsql/DB_PASSWORD=\"b9c3q46-9bv08967\"/g" \
    \
    -e "s/^API_ACCOUNT_ROH_HOST=example.com/API_ACCOUNT_ROH_HOST=de2ef01d.dev.favorit/g" \
    -e "s/^API_ACCOUNT_ROH_PORT=6666/API_ACCOUNT_ROH_PORT=10102/g" \
    \
    -e "s/^BETGAMES_DISCONNECT_TIME=0/BETGAMES_DISCONNECT_TIME=10/g" \
    \
    ./.env.testing

echo "" >> ./.env.testing
echo "TEST_USER_ID=1555" >> ./.env.testing
echo "TEST_PARTNER_ID=1" >> ./.env.testing
echo "TEST_CASHEDESK=-5" >> ./.env.testing
