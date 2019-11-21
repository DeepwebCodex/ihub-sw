#!/usr/bin/env bash

cp ./.env.example ./.env
sed -i -e "s/^APP_URL=http:\/\/localhost/APP_URL=https:\/\/ihub.bet/g" \
    -e "s/^APP_ENV=null/APP_ENV=production/g" \
    -e "s/^APP_KEY=example/APP_KEY=\"base64:axrMo7RS1BV9f589cGtb+iQejqRmQdeI071MMMIleE4=\"/g" \
    -e "s/^APP_DEBUG=null/APP_DEBUG=false/g" \
    -e "s/^APP_LOG_LEVEL=null/APP_LOG_LEVEL=warning/g" \
    -e "s/^APP_REQUEST_DEBUG=null/APP_REQUEST_DEBUG=true/g" \
    -e "s/^LOG_EXTERNAL_REQUESTS=null/LOG_EXTERNAL_REQUESTS=false/g" \
    \
    -e "s/^LOG_TRIM_RESPONSE_SIZE=null/LOG_TRIM_RESPONSE_SIZE=255/g" \
    \
    -e "s/^SESSION_DRIVER=null/SESSION_DRIVER=redis/g" \
    -e "s/^CACHE_DRIVER=null/CACHE_DRIVER=redis/g" \
    -e "s/^LOG_DRIVER=null/LOG_DRIVER=rabbit/g" \
    -e "s/^BROADCAST_DRIVER=null/BROADCAST_DRIVER=log/g" \
    -e "s/^QUEUE_CONNECTION=null/QUEUE_CONNECTION=sync/g" \
    \
    -e "s/^DYNAMIC_SCHEDULER_API_LOGIN=login/DYNAMIC_SCHEDULER_API_LOGIN=dynamic_scheduler_api_login/g" \
    -e "s/^DYNAMIC_SCHEDULER_API_PASSWORD=\"password\"/DYNAMIC_SCHEDULER_API_PASSWORD=\"gBEWPkx4yGDCZj0P\"/g" \
    \
    -e "/DB_HOST=example.com/d" \
    -e "/DB_PORT=6666/d" \
    -e "/DB_DATABASE=db/d" \
    -e "/DB_USERNAME=pgsql/d" \
    -e "/DB_PASSWORD=pgsql/d" \
    \
    -e "s/^#REDIS_HOST=example.com/REDIS_HOST=redis.ihub.rancher.internal/g" \
    -e "s/^#REDIS_PORT=6666/REDIS_PORT=6379/g" \
    -e "s/^#REDIS_PREFIX=redis_prefix/REDIS_PREFIX=ihubGrid:ihub-sw/g" \
    \
    -e "s/^#LOG_RABBIT_HOST=example.com/LOG_RABBIT_HOST=rabbitmq-server.ihub.rancher.internal/g" \
    -e "s/^#LOG_RABBIT_PORT=6666/LOG_RABBIT_PORT=5672/g" \
    -e "s/^#LOG_RABBIT_USER=log_rabbit/LOG_RABBIT_USER=ihub/g" \
    -e "s/^#LOG_RABBIT_PASS=log_rabbit/LOG_RABBIT_PASS=\"8jm7JMBmCsqp\"/g" \
    -e "s/^#LOG_RABBIT_VHOST=null/LOG_RABBIT_VHOST=\"ihub_sw\"/g" \
    \
    -e "s/^API_ACCOUNT_ROH_HOST=example.com/API_ACCOUNT_ROH_HOST=api-account-roh.external.rancher.internal/g" \
    -e "s/^API_ACCOUNT_ROH_PORT=6666/API_ACCOUNT_ROH_PORT=7767/g" \
    \
    -e "s/^#API_ACCOUNT_ROH_HOST_15=example.com/API_ACCOUNT_ROH_HOST_15=e-proxy.en1.favorit/g" \
    -e "s/^#API_ACCOUNT_ROH_PORT_15=6666/API_ACCOUNT_ROH_PORT_15=10002/g" \
    \
    -e "s/^#API_ACCOUNT_ROH_HOST_16=example.com/API_ACCOUNT_ROH_HOST_16=e-proxy.ua7.favorit/g" \
    -e "s/^#API_ACCOUNT_ROH_PORT_16=6666/API_ACCOUNT_ROH_PORT_16=10002/g" \
    \
    -e "s/^#API_ACCOUNT_ROH_HOST_34=example.com/API_ACCOUNT_ROH_HOST_34=e-proxy.tm1.favorit/g" \
    -e "s/^#API_ACCOUNT_ROH_PORT_34=6666/API_ACCOUNT_ROH_PORT_34=10002/g" \
    \
    -e "s/^#API_ACCOUNT_ROH_HOST_42=example.com/API_ACCOUNT_ROH_HOST_42=e-proxy.am2.favorit/g" \
    -e "s/^#API_ACCOUNT_ROH_PORT_42=6666/API_ACCOUNT_ROH_PORT_42=10002/g" \
    \
    -e "s/^#API_ACCOUNT_ROH_HOST_48=example.com/API_ACCOUNT_ROH_HOST_48=e-proxy.iq1.favorit/g" \
    -e "s/^#API_ACCOUNT_ROH_PORT_48=6666/API_ACCOUNT_ROH_PORT_48=10002/g" \
    \
    -e "s/^#API_ACCOUNT_ROH_HOST_49=example.com/API_ACCOUNT_ROH_HOST_49=e-proxy.sk.favorit/g" \
    -e "s/^#API_ACCOUNT_ROH_PORT_49=6666/API_ACCOUNT_ROH_PORT_49=10002/g" \
    \
    -e "s/^#API_ACCOUNT_ROH_HOST_59=example.com/API_ACCOUNT_ROH_HOST_59=e-proxy.iq2.favorit/g" \
    -e "s/^#API_ACCOUNT_ROH_PORT_59=6666/API_ACCOUNT_ROH_PORT_59=10002/g" \
    \
    -e "s/^#API_ACCOUNT_ROH_HOST_91=example.com/API_ACCOUNT_ROH_HOST_91=e-proxy.en2.favorit/g" \
    -e "s/^#API_ACCOUNT_ROH_PORT_91=6666/API_ACCOUNT_ROH_PORT_91=10002/g" \
    \
    -e "s/^GAME_SESSION_API_LOGIN=game_session_api_login/GAME_SESSION_API_LOGIN=\"t4ewr\$zAF@#u6esp\"/g" \
    -e "s/^GAME_SESSION_API_PASSWORD=game_session_api_password/GAME_SESSION_API_PASSWORD=\"t4ewr\$zAF@#u6esp\"/g" \
    -e "s/^GAME_SESSION_STORAGE_SECRET=game_session_storage_secret/GAME_SESSION_STORAGE_SECRET=gBEWPkx4yGDCZj0P/g" \
    -e "s/^GAME_SESSION_STORAGE_KEY_PREFIX=game_session_storage_key_prefix/GAME_SESSION_STORAGE_KEY_PREFIX=game_sessions/g" \
    -e "s/^GAME_SESSION_STORAGE_TTL=900/GAME_SESSION_STORAGE_TTL=86400/g" \
    \
    -e "s/^BETGAMES_DISCONNECT_TIME=0/BETGAMES_DISCONNECT_TIME=10/g" \
    \
    -e "s/^#RABBITMQ_HOST=example.com/RABBITMQ_HOST=rabbitmq-server.elkr.rancher.internal/g" \
    -e "s/^#RABBITMQ_PORT=6666/RABBITMQ_PORT=5672/g" \
    -e "s/^#RABBITMQ_USER=user/RABBITMQ_USER=communication/g" \
    -e "s/^#RABBITMQ_PASS=pass/RABBITMQ_PASS=\"e5MJkbWRVNvdpzGD\"/g" \
    -e "s/^#RABBITMQ_PREFIX=/RABBITMQ_PREFIX=\"\"/g" \
    -e "s/^#RABBITMQ_VHOST=null/RABBITMQ_VHOST=\"communication\"/g" \
    \
    -e "s/^COMMUNICATION_PROTOCOL_ENABLE=false/COMMUNICATION_PROTOCOL_ENABLE=false/g" \
    -e "s/^TRANSACTION_COMMUNICATION_PROTOCOL_ENABLE=false/TRANSACTION_COMMUNICATION_PROTOCOL_ENABLE=false/g" \
    \
    -e "s/^ELASTICSEARCH_HOST=\"http:\/\/localhost:9200\"/ELASTICSEARCH_HOST=\"http:\/\/elasticsearch.elkr.rancher.internal:9200\"/g" \
    \
    ./.env


echo "" >> ./.env
cat /root/ihub/env.ihub >> .env
