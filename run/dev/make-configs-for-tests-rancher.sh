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
    -e "s/^CACHE_DRIVER=null/CACHE_DRIVER=redis/g" \
    -e "s/^LOG_DRIVER=null/LOG_DRIVER=rabbit/g" \
    -e "s/^BROADCAST_DRIVER=null/BROADCAST_DRIVER=log/g" \
    -e "s/^QUEUE_CONNECTION=null/QUEUE_CONNECTION=sync/g" \
    \
    -e "s/^DB_HOST=example.com/DB_HOST=de2db02d.dev.favorit/g" \
    -e "s/^DB_PORT=6666/DB_PORT=6432/g" \
    -e "s/^DB_DATABASE=db/DB_DATABASE=ihub/g" \
    -e "s/^DB_USERNAME=pgsql/DB_USERNAME=u_ihub/g" \
    -e "s/^DB_PASSWORD=pgsql/DB_PASSWORD=\"b9c3q46-9bv08967\"/g" \
    \
    -e "s/^#REDIS_HOST=example.com/REDIS_HOST=10.141.11.56/g" \
    -e "s/^#REDIS_PORT=6666/REDIS_PORT=1379/g" \
    -e "s/^#REDIS_PREFIX=redis_prefix/REDIS_PREFIX=ihubGrid:ihub-sw/g" \
    \
    -e "s/^#LOG_RABBIT_HOST=example.com/LOG_RABBIT_HOST=10.141.11.56/g" \
    -e "s/^#LOG_RABBIT_PORT=6666/LOG_RABBIT_PORT=5672/g" \
    -e "s/^#LOG_RABBIT_USER=log_rabbit/LOG_RABBIT_USER=ihub/g" \
    -e "s/^#LOG_RABBIT_PASS=log_rabbit/LOG_RABBIT_PASS=\"ihub\"/g" \
    -e "s/^#LOG_RABBIT_VHOST=null/LOG_RABBIT_VHOST=\"ihub_sw\"/g" \
    \
    -e "s/^API_ACCOUNT_ROH_HOST=example.com/API_ACCOUNT_ROH_HOST=de2ef01d.dev.favorit/g" \
    -e "s/^API_ACCOUNT_ROH_PORT=6666/API_ACCOUNT_ROH_PORT=10102/g" \
    \
    -e "s/^BETGAMES_DISCONNECT_TIME=0/BETGAMES_DISCONNECT_TIME=10/g" \
    \
    -e "s/^#RABBITMQ_HOST=example.com/RABBITMQ_HOST=10.141.11.56/g" \
    -e "s/^#RABBITMQ_PORT=6666/RABBITMQ_PORT=5672/g" \
    -e "s/^#RABBITMQ_USER=user/RABBITMQ_USER=communication/g" \
    -e "s/^#RABBITMQ_PASS=pass/RABBITMQ_PASS=\"communication\"/g" \
    -e "s/^#RABBITMQ_VHOST=null/RABBITMQ_VHOST=\"communication\"/g" \
    \
    -e "s/^COMMUNICATION_PROTOCOL_ENABLE=false/COMMUNICATION_PROTOCOL_ENABLE=true/g" \
    -e "s/^TRANSACTION_COMMUNICATION_PROTOCOL_ENABLE=false/TRANSACTION_COMMUNICATION_PROTOCOL_ENABLE=true/g" \
    \
    -e "s/^ACCOUNT_MANAGER_MOCK_IS_ENABLED=true/ACCOUNT_MANAGER_MOCK_IS_ENABLED=true/g" \
    \
    -e "s/^API_CARDS_ROH_HOST=example.com/API_CARDS_ROH_HOST=de2ef01d.dev.favorit/g" \
    -e "s/^API_CARDS_ROH_PORT=6666/API_CARDS_ROH_PORT=10102/g" \
    \
    -e "s/^API_CONFAGENT_ROH_HOST=example.com/API_CONFAGENT_ROH_HOST=de2ef01d.dev.favorit/g" \
    -e "s/^API_CONFAGENT_ROH_PORT=6666/API_CONFAGENT_ROH_PORT=10102/g" \
    \
    ./.env.testing


echo "" >> ./.env.testing
echo "TEST_USER_ID=1555" >> ./.env.testing
echo "TEST_PARTNER_ID=1" >> ./.env.testing
echo "TEST_CASHEDESK=-5" >> ./.env.testing
