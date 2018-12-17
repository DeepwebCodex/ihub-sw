#!/usr/bin/env bash

cp ./.env.example ./.env
sed -i -e "s/^APP_URL=http:\/\/localhost/APP_URL=http:\/\/ihub.favbet.dev/g" \
    -e "s/^APP_ENV=example/APP_ENV=dev/g" \
    -e "s/^APP_KEY=example/APP_KEY=\"base64:axrMo7RS1BV9f589cGtb+iQejqRmQdeI071MMMIleE4=\"/g" \
    -e "s/^APP_DEBUG=true/APP_DEBUG=true/g" \
    -e "s/^APP_LOG_LEVEL=debug/APP_LOG_LEVEL=debug/g" \
    -e "s/^APP_REQUEST_DEBUG=true/APP_REQUEST_DEBUG=true/g" \
    -e "s/^LOG_EXTERNAL_REQUESTS=false/LOG_EXTERNAL_REQUESTS=true/g" \
    -e "s/^LOG_TRIM_RESPONSE_SIZE=255/LOG_TRIM_RESPONSE_SIZE=2048/g" \
    \
    -e "s/^SESSION_DRIVER=file/SESSION_DRIVER=redis/g" \
    -e "s/^CACHE_DRIVER=file/CACHE_DRIVER=redis/g" \
    -e "s/^LOG_DRIVER=file/LOG_DRIVER=rabbit/g" \
    -e "s/^BROADCAST_DRIVER=log/BROADCAST_DRIVER=log/g" \
    -e "s/^QUEUE_DRIVER=sync/QUEUE_DRIVER=sync/g" \
    \
    -e "s/^DB_HOST=example.com/DB_HOST=de2db02d.dev.favorit/g" \
    -e "s/^DB_PORT=5432/DB_PORT=6432/g" \
    -e "s/^DB_DATABASE=db/DB_DATABASE=ihub/g" \
    -e "s/^DB_USERNAME=pgsql/DB_USERNAME=u_ihub/g" \
    -e "s/^DB_PASSWORD=pgsql/DB_PASSWORD=\"b9c3q46-9bv08967\"/g" \
    \
    -e "s/^REDIS_HOST=example.com/REDIS_HOST=redis-ihub.redis.rancher.internal/g" \
    -e "s/^REDIS_PORT=6379/REDIS_PORT=6379/g" \
    -e "s/^REDIS_PREFIX=redis_prefix/REDIS_PREFIX=ihubGrid:ihub-sw/g" \
    \
    -e "s/^LOG_RABBIT_HOST=example.com/LOG_RABBIT_HOST=rabbitmq-server.elkr.rancher.internal/g" \
    -e "s/^LOG_RABBIT_PORT=6666/LOG_RABBIT_PORT=5672/g" \
    -e "s/^LOG_RABBIT_USER=log_rabbit/LOG_RABBIT_USER=ihub/g" \
    -e "s/^LOG_RABBIT_PASS=log_rabbit/LOG_RABBIT_PASS=\"ihub\"/g" \
    -e "s/^LOG_RABBIT_VHOST=\"\/\"/LOG_RABBIT_VHOST=\"ihub_sw\"/g" \
    \
    -e "s/^API_ACCOUNT_ROH_HOST=example.com/API_ACCOUNT_ROH_HOST=de2ef01d.dev.favorit/g" \
    -e "s/^API_ACCOUNT_ROH_PORT=6666/API_ACCOUNT_ROH_PORT=10102/g" \
    \
    -e "s/^API_ACCOUNT_ROH_HOST_59__59=example.com/API_ACCOUNT_ROH_HOST_59__59=de2cs01d.dev.favorit/g" \
    -e "s/^API_ACCOUNT_ROH_PORT_59__59=6666/API_ACCOUNT_ROH_PORT_59__59=10007/g" \
    \
    -e "s/^API_ACCOUNT_ROH_HOST_51__51=example.com/API_ACCOUNT_ROH_HOST_51__51=de2ef01d.dev.favorit/g" \
    -e "s/^API_ACCOUNT_ROH_PORT_51__51=6666/API_ACCOUNT_ROH_PORT_51__51=10102/g" \
    \
    -e "s/^GAME_SESSION_API_LOGIN=game_session_api_login/GAME_SESSION_API_LOGIN=\"t4ewr\$zAF@#u6esp\"/g" \
    -e "s/^GAME_SESSION_API_PASSWORD=game_session_api_password/GAME_SESSION_API_PASSWORD=\"t4ewr\$zAF@#u6esp\"/g" \
    -e "s/^GAME_SESSION_STORAGE_SECRET=game_session_storage_secret/GAME_SESSION_STORAGE_SECRET=gBEWPkx4yGDCZj0P/g" \
    -e "s/^GAME_SESSION_STORAGE_KEY_PREFIX=game_session_storage_key_prefix/GAME_SESSION_STORAGE_KEY_PREFIX=game_sessions/g" \
    -e "s/^GAME_SESSION_STORAGE_TTL=900/GAME_SESSION_STORAGE_TTL=86400/g" \
    \
    -e "s/^BETGAMES_DISCONNECT_TIME=0/BETGAMES_DISCONNECT_TIME=10/g" \
    \
    -e "s/^DYNAMIC_SCHEDULER_API_LOGIN=login/DYNAMIC_SCHEDULER_API_LOGIN=dynamic_scheduler_api_login/g" \
    -e "s/^DYNAMIC_SCHEDULER_API_PASSWORD=\"password\"/DYNAMIC_SCHEDULER_API_PASSWORD=\"gBEWPkx4yGDCZj0P\"/g" \
    \
    -e "s/^RABBITMQ_HOST=example.com/RABBITMQ_HOST=rabbitmq-server.elkr.rancher.internal/g" \
    -e "s/^RABBITMQ_PORT=6666/RABBITMQ_PORT=5672/g" \
    -e "s/^RABBITMQ_USER=user/RABBITMQ_USER=communication/g" \
    -e "s/^RABBITMQ_PASS=pass/RABBITMQ_PASS=\"communication\"/g" \
    -e "s/^RABBITMQ_VHOST=\"\/\"/RABBITMQ_VHOST=\"communication\"/g" \
    \
    -e "s/^COMMUNICATION_PROTOCOL_ENABLE=false/COMMUNICATION_PROTOCOL_ENABLE=true/g" \
    -e "s/^TRANSACTION_COMMUNICATION_PROTOCOL_ENABLE=false/TRANSACTION_COMMUNICATION_PROTOCOL_ENABLE=true/g" \
    \
    -e "s/^ELASTICSEARCH_HOST=\"http:\/\/localhost:9200\"/ELASTICSEARCH_HOST=\"http:\/\/elasticsearch.elkr.rancher.internal:9200\"/g" \
    \
    -e "s/^ACCOUNT_MANAGER_MOCK_IS_ENABLED=true/ACCOUNT_MANAGER_MOCK_IS_ENABLED=true/g" \
    \
    -e "s/^API_CONFAGENT_ROH_HOST=example.com/API_CONFAGENT_ROH_HOST=confagent.external.rancher.internal/g" \
    -e "s/^API_CONFAGENT_ROH_PORT=6666/API_CONFAGENT_ROH_PORT=10102/g" \
    \
    -e "s/^API_CASHDESK_HOST=example.com/API_CASHDESK_HOST=cms.external.rancher.internal/g" \
    -e "s/^API_CASHDESK_PORT=6666/API_CASHDESK_PORT=10102/g" \
    \
    -e "s/^API_CASH_DESK_ROH_HOST=example.com/API_CASH_DESK_ROH_HOST=e-proxy.devel/g" \
    -e "s/^API_CASH_DESK_ROH_PORT=6666/API_CASH_DESK_ROH_PORT=7767/g" \
    \
    ./.env
