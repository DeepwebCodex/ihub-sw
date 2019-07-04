#!/usr/bin/env bash

cp ./.env.example ./.env.testing
sed -i -e "s/^APP_ENV=null/APP_ENV=testing/g" \
    -e "s/^APP_KEY=example/APP_KEY=\"base64:axrMo7RS1BV9f589cGtb+iQejqRmQdeI071MMMIleE4=\"/g" \
    -e "s/^APP_DEBUG=null/APP_DEBUG=true/g" \
    -e "s/^APP_LOG_LEVEL=null/APP_LOG_LEVEL=debug/g" \
    -e "s/^APP_REQUEST_DEBUG=null/APP_REQUEST_DEBUG=true/g" \
    -e "s/^LOG_EXTERNAL_REQUESTS=null/LOG_EXTERNAL_REQUESTS=true/g" \
    \
    -e "s/^SESSION_DRIVER=null/SESSION_DRIVER=file/g" \
    -e "s/^CACHE_DRIVER=null/CACHE_DRIVER=redis/g" \
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
    -e "s/^DB_HOST_ERLYBET=example.com/DB_HOST_ERLYBET=de2db02d.dev.favorit/g" \
    -e "s/^DB_PORT_ERLYBET=5432/DB_PORT_ERLYBET=5432/g" \
    -e "s/^DB_DATABASE_ERLYBET=db/DB_DATABASE_ERLYBET=erlybet/g" \
    -e "s/^DB_USERNAME_ERLYBET=pgsql/DB_USERNAME_ERLYBET=g_develop/g" \
    -e "s/^DB_PASSWORD_ERLYBET=pgsql/DB_PASSWORD_ERLYBET=hb8g7t34fbv09to/g" \
    \
    -e "s/^#REDIS_HOST=example.com/REDIS_HOST=de2red01d.dev.favorit/g" \
    -e "s/^#REDIS_PORT=6666/REDIS_PORT=6379/g" \
    -e "s/^#REDIS_PREFIX=redis_prefix/REDIS_PREFIX=ihubGrid:ihub-sw/g" \
    \
    -e "s/^API_ACCOUNT_ROH_HOST=example.com/API_ACCOUNT_ROH_HOST=de2ef01d.dev.favorit/g" \
    -e "s/^API_ACCOUNT_ROH_PORT=6666/API_ACCOUNT_ROH_PORT=10102/g" \
    \
    -e "s/^#API_ACCOUNT_ROH_HOST_4=example.com/API_ACCOUNT_ROH_HOST_4=by1ef01d.dev.favorit/g" \
    -e "s/^#API_ACCOUNT_ROH_PORT_4=6666/API_ACCOUNT_ROH_PORT_4=10102/g" \
    \
    -e "s/^GAME_SESSION_API_LOGIN=game_session_api_login/GAME_SESSION_API_LOGIN=\"t4ewr\$zAF@#u6esp\"/g" \
    -e "s/^GAME_SESSION_API_PASSWORD=game_session_api_password/GAME_SESSION_API_PASSWORD=\"t4ewr\$zAF@#u6esp\"/g" \
    -e "s/^GAME_SESSION_STORAGE_SECRET=game_session_storage_secret/GAME_SESSION_STORAGE_SECRET=gBEWPkx4yGDCZj0P/g" \
    -e "s/^GAME_SESSION_STORAGE_KEY_PREFIX=game_session_storage_key_prefix/GAME_SESSION_STORAGE_KEY_PREFIX=game_sessions/g" \
    -e "s/^GAME_SESSION_STORAGE_TTL=900/GAME_SESSION_STORAGE_TTL=86400/g" \
    \
    -e "s/^BETGAMES_DISCONNECT_TIME=0/BETGAMES_DISCONNECT_TIME=10/g" \
    \
    -e "s/^ACCOUNT_MANAGER_MOCK_IS_ENABLED=true/ACCOUNT_MANAGER_MOCK_IS_ENABLED=true/g" \
    \
    -e "s/^API_CARDS_ROH_HOST=example.com/API_CARDS_ROH_HOST=de2ef01d.dev.favorit/g" \
    -e "s/^API_CARDS_ROH_PORT=6666/API_CARDS_ROH_PORT=10102/g" \
    \
    -e "s/^API_CONFAGENT_ROH_HOST=example.com/API_CONFAGENT_ROH_HOST=de2ef01d.dev.favorit/g" \
    -e "s/^API_CONFAGENT_ROH_PORT=6666/API_CONFAGENT_ROH_PORT=10102/g" \
    \
    -e "s/^API_CASHDESK_HOST=example.com/API_CASHDESK_HOST=icms.ihub.favbet.devel/g" \
    -e "s/^API_CASHDESK_PORT=6666/API_CASHDESK_PORT=80/g" \
    \
    ./.env.testing

echo "" >> ./.env.testing
echo "TEST_USER_ID=1555" >> ./.env.testing
echo "TEST_PARTNER_ID=1" >> ./.env.testing
echo "TEST_CASHEDESK=-5" >> ./.env.testing
