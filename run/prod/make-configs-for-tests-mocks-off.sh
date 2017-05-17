#!/usr/bin/env bash

cp ./.env.example ./.env
cp ./.env.example ./.env.testing
sed -i -e "s/^APP_URL=http:\/\/localhost/APP_URL=http:\/\/ihub.favbet.dev/g" \
       -e "s/^APP_ENV=local/APP_ENV=testing/g" \
       -e "s/^APP_DEBUG=true/APP_DEBUG=true/g" \
       -e "s/^APP_REQUEST_DEBUG=true/APP_REQUEST_DEBUG=true/g" \
       -e "s/^APP_LOG_LEVEL=debug/APP_LOG_LEVEL=debug/g" \
        \
       -e "s/^SESSION_DRIVER=file/SESSION_DRIVER=redis/g" \
       -e "s/^CACHE_DRIVER=file/CACHE_DRIVER=redis/g" \
       -e "s/^LOG_DRIVER=file/LOG_DRIVER=file/g" \
       -e "s/^BROADCAST_DRIVER=log/BROADCAST_DRIVER=log/g" \
       -e "s/^QUEUE_DRIVER=sync/QUEUE_DRIVER=sync/g" \
        \
       -e "s/^DB_HOST=example.com/DB_HOST=de2db02d.dev.favorit/g" \
       -e "s/^DB_PORT=5432/DB_PORT=5432/g" \
       -e "s/^DB_DATABASE=db/DB_DATABASE=ihub/g" \
       -e "s/^DB_USERNAME=pgsql/DB_USERNAME=u_ihub/g" \
       -e "s/^DB_PASSWORD=pgsql/DB_PASSWORD=\"b9c3q46-9bv08967\"​/g" \
        \
       -e "s/^DB_HOST_LINE=example.com/DB_HOST_LINE=de2db02d.dev.favorit/g" \
       -e "s/^DB_PORT_LINE=5432/DB_PORT_LINE=5432/g" \
       -e "s/^DB_DATABASE_LINE=db/DB_DATABASE_LINE=line/g" \
       -e "s/^DB_USERNAME_LINE=pgsql/DB_USERNAME_LINE=g_develop/g" \
       -e "s/^DB_PASSWORD_LINE=pgsql/DB_PASSWORD_LINE=hb8g7t34fbv09to/g" \
        \
       -e "s/^DB_HOST_TRANS=example.com/DB_HOST_TRANS=de2db02d.dev.favorit/g" \
       -e "s/^DB_PORT_TRANS=5432/DB_PORT_TRANS=5432/g" \
       -e "s/^DB_DATABASE_TRANS=db/DB_DATABASE_TRANS=trans/g" \
       -e "s/^DB_USERNAME_TRANS=pgsql/DB_USERNAME_TRANS=g_develop/g" \
       -e "s/^DB_PASSWORD_TRANS=pgsql/DB_PASSWORD_TRANS=hb8g7t34fbv09to/g" \
        \
       -e "s/^REDIS_PREFIX=integrations_cms/REDIS_PREFIX=integrations_hub/g" \
       -e "s/^REDIS_HOST=example.com/REDIS_HOST=de2red01d.dev.favorit/g" \
       -e "s/^REDIS_PORT=6379/REDIS_PORT=6379/g" \
        \
       -e "s/^LOG_RABBIT_HOST=example.com/LOG_RABBIT_HOST=rabbitmq-server/g" \
       -e "s/^LOG_RABBIT_PORT=5672/LOG_RABBIT_PORT=5672/g" \
       -e "s/^LOG_RABBIT_USER=log_rabbit/LOG_RABBIT_USER=ihub/g" \
       -e "s/^LOG_RABBIT_PASS=log_rabbit/LOG_RABBIT_PASS=\"8jm7JMBmCsqp\"/g" \
       -e "s/^LOG_RABBIT_PREFIX=log_rabbit_prefix/LOG_RABBIT_PREFIX=\"\"/g" \
        \
       -e "s/^API_ACCOUNT_ROH_HOST=example.com/API_ACCOUNT_ROH_HOST=de2ef01d.dev.favorit/g" \
       -e "s/^API_ACCOUNT_ROH_PORT=6666/API_ACCOUNT_ROH_PORT=10102/g" \
       -e "s/^API_ACCOUNT_SESSION_HOST=example.com/API_ACCOUNT_SESSION_HOST=e-proxy.dev/g" \
       -e "s/^API_ACCOUNT_SESSION_PORT=6666/API_ACCOUNT_SESSION_PORT=8061/g" \
       -e "s/^API_ACCOUNT_OP_HOST=example.com/API_ACCOUNT_OP_HOST=e-proxy.dev/g" \
       -e "s/^API_ACCOUNT_OP_PORT=6666/API_ACCOUNT_OP_PORT=8008/g" \
       -e "s/^API_CARDS_ROH_HOST=example.com/API_CARDS_ROH_HOST=e-proxy.dev/g" \
       -e "s/^API_CARDS_ROH_PORT=6666/API_CARDS_ROH_PORT=7767/g" \
       -e "s/^API_CASH_DESK_ROH_HOST=example.com/API_CASH_DESK_ROH_HOST=e-proxy.dev/g" \
       -e "s/^API_CASH_DESK_ROH_PORT=6666/API_CASH_DESK_ROH_PORT=7767/g" \
        \
       -e "s/^API_HAZLE_SESSIONS=\"example1.com:6666;example.com2:6666\"/API_HAZLE_SESSIONS=\"de2ef01d.dev.favorit:5701;de2ef03d.dev.favorit:5701\"/g" \
        \
       -e "s/^GAME_SESSION_API_LOGIN=game_session_api_login/GAME_SESSION_API_LOGIN=\"t4ewr\$zAF@#u6esp\"/g" \
       -e "s/^GAME_SESSION_API_PASSWORD=game_session_api_password/GAME_SESSION_API_PASSWORD=\"t4ewr\$zAF@#u6esp\"/g" \
       -e "s/^GAME_SESSION_STORAGE_SECRET=game_session_storage_secret/GAME_SESSION_STORAGE_SECRET=gBEWPkx4yGDCZj0P/g" \
       -e "s/^GAME_SESSION_STORAGE_KEY_PREFIX=game_session_storage_key_prefix/GAME_SESSION_STORAGE_KEY_PREFIX=game_sessions/g" \
       -e "s/^GAME_SESSION_STORAGE_TTL=900/GAME_SESSION_STORAGE_TTL=900/g" \
        \
       -e "s/^ACCOUNT_MANAGER_MOCK_IS_ENABLED=true/ACCOUNT_MANAGER_MOCK_IS_ENABLED=false/g" \
        \
       ./.env.testing

echo "" >> ./.env.testing
echo "TEST_USER_ID=89" >> ./.env.testing
echo "TEST_PARTNER_ID=1" >> ./.env.testing
echo "TEST_CASHEDESK=-5" >> ./.env.testing

