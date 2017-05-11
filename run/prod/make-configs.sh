#!/usr/bin/env bash

echo "--- Preparing config.."
chmod -R 777 ./storage && chmod -R 777 ./bootstrap/cache

cp ./.env.example ./.env
sed -i -e "s/^APP_URL=http:\/\/localhost/APP_URL=http:\/\/ihub.bet/g" \
       -e "s/^APP_ENV=local/APP_ENV=production/g" \
       -e "s/^APP_DEBUG=true/APP_DEBUG=false/g" \
       -e "s/^APP_REQUEST_DEBUG=true/APP_REQUEST_DEBUG=true/g" \
       -e "s/^APP_LOG_LEVEL=debug/APP_LOG_LEVEL=warning/g" \
        \
       -e "s/^SESSION_DRIVER=file/SESSION_DRIVER=redis/g" \
       -e "s/^CACHE_DRIVER=file/CACHE_DRIVER=redis/g" \
       -e "s/^LOG_DRIVER=file/LOG_DRIVER=rabbit/g" \
       -e "s/^BROADCAST_DRIVER=log/BROADCAST_DRIVER=log/g" \
       -e "s/^QUEUE_DRIVER=sync/QUEUE_DRIVER=sync/g" \
        \
       -e "s/^DB_HOST=example.com//g" \
       -e "s/^DB_PORT=5432//g" \
       -e "s/^DB_DATABASE=db//g" \
       -e "s/^DB_USERNAME=pgsql//g" \
       -e "s/^DB_PASSWORD=pgsql//g" \
        \
       -e "s/^DB_HOST_LINE=example.com//g" \
       -e "s/^DB_PORT_LINE=5432//g" \
       -e "s/^DB_DATABASE_LINE=db//g" \
       -e "s/^DB_USERNAME_LINE=pgsql//g" \
       -e "s/^DB_PASSWORD_LINE=pgsql//g" \
        \
       -e "s/^DB_HOST_TRANS=example.com//g" \
       -e "s/^DB_PORT_TRANS=5432//g" \
       -e "s/^DB_DATABASE_TRANS=db//g" \
       -e "s/^DB_USERNAME_TRANS=pgsql//g" \
       -e "s/^DB_PASSWORD_TRANS=pgsql//g" \
        \
       -e "s/^REDIS_PREFIX=integrations_cms/REDIS_PREFIX=integrations_hub/g" \
       -e "s/^REDIS_HOST=example.com/REDIS_HOST=dc02re03/g" \
       -e "s/^REDIS_PORT=6379/REDIS_PORT=6379/g" \
        \
       -e "s/^LOG_RABBIT_HOST=example.com/LOG_RABBIT_HOST=rabbitmq-server/g" \
       -e "s/^LOG_RABBIT_PORT=5672/LOG_RABBIT_PORT=5672/g" \
       -e "s/^LOG_RABBIT_USER=log_rabbit/LOG_RABBIT_USER=ihub/g" \
       -e "s/^LOG_RABBIT_PASS=log_rabbit/LOG_RABBIT_PASS=\"8jm7JMBmCsqp\"/g" \
       -e "s/^LOG_RABBIT_PREFIX=log_rabbit_prefix/LOG_RABBIT_PREFIX=\"\"/g" \
        \
       -e "s/^API_ACCOUNT_ROH_HOST=example.com/API_ACCOUNT_ROH_HOST=api-account-roh/g" \
       -e "s/^API_ACCOUNT_ROH_PORT=6666/API_ACCOUNT_ROH_PORT=7777/g" \
       -e "s/^API_ACCOUNT_OP_HOST=example.com/API_ACCOUNT_OP_HOST=api-account-op/g" \
       -e "s/^API_ACCOUNT_OP_PORT=6666/API_ACCOUNT_OP_PORT=8008/g" \
       -e "s/^API_CARDS_ROH_HOST=example.com/API_CARDS_ROH_HOST=api-cards-roh/g" \
       -e "s/^API_CARDS_ROH_PORT=6666/API_CARDS_ROH_PORT=7767/g" \
       -e "s/^API_CASH_DESK_ROH_HOST=example.com/API_CASH_DESK_ROH_HOST=api-cashdesk-roh/g" \
       -e "s/^API_CASH_DESK_ROH_PORT=6666/API_CASH_DESK_ROH_PORT=7767/g" \
        \
       -e "s/^API_HAZLE_SESSIONS=\"example1.com:6666;example.com2:6666\"/API_HAZLE_SESSIONS=\"de1ef01p:5701;de1ef02p:5701;de1ef03p:5701;de1ef04p:5701;de1ef05p:5701;de1ef06p:5701\"/g" \
        \
       -e "s/^GAME_SESSION_API_LOGIN=game_session_api_login/GAME_SESSION_API_LOGIN=\"t4ewr\$zAF@#u6esp\"/g" \
       -e "s/^GAME_SESSION_API_PASSWORD=game_session_api_password/GAME_SESSION_API_PASSWORD=\"t4ewr\$zAF@#u6esp\"/g" \
       -e "s/^GAME_SESSION_STORAGE_SECRET=game_session_storage_secret/GAME_SESSION_STORAGE_SECRET=gBEWPkx4yGDCZj0P/g" \
       -e "s/^GAME_SESSION_STORAGE_KEY_PREFIX=game_session_storage_key_prefix/GAME_SESSION_STORAGE_KEY_PREFIX=game_sessions/g" \
       -e "s/^GAME_SESSION_STORAGE_TTL=900/GAME_SESSION_STORAGE_TTL=900/g" \
        \
       -e "s/^BETGAMES_DISCONNECT_TIME=0/BETGAMES_DISCONNECT_TIME=10/g" \
        \
       -e "s/^DYNAMIC_SCHEDULER_API_LOGIN=login/DYNAMIC_SCHEDULER_API_LOGIN=dynamic_scheduler_api_login/g" \
       -e "s/^DYNAMIC_SCHEDULER_API_PASSWORD=\"password\"/DYNAMIC_SCHEDULER_API_PASSWORD=\"gBEWPkx4yGDCZj0P\"/g" \
        \
       ./.env

echo "--- Installing composer.."
echo ">> composer install"
composer install

echo "--- Optimizing project.."

echo ">> php artisan route:cache"
php artisan route:cache

cat /root/ihub/env.ihub >> .env
