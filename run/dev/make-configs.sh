#!/usr/bin/env bash

cp ./.env.example ./.env
sed -i -e "s/^APP_URL=http:\/\/localhost/APP_URL=http:\/\/ihub.favbet.dev/g" \
       -e "s/^APP_ENV=local/APP_ENV=dev/g" \
       -e "s/^APP_DEBUG=true/APP_DEBUG=true/g" \
       -e "s/^APP_REQUEST_DEBUG=true/APP_REQUEST_DEBUG=true/g" \
       -e "s/^APP_LOG_LEVEL=debug/APP_LOG_LEVEL=debug/g" \
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
       -e "s/^DB_PASSWORD=pgsql/DB_PASSWORD=\"b9c3q46-9bv08967\"​/g" \
        \
       -e "s/^REDIS_HOST=example.com/REDIS_HOST=de2red01d.dev.favorit/g" \
       -e "s/^REDIS_PORT=6379/REDIS_PORT=6379/g" \
       -e "s/^REDIS_PREFIX=integrations_cms/REDIS_PREFIX=integrations_hub/g" \
        \
       -e "s/^LOG_RABBIT_HOST=example.com/LOG_RABBIT_HOST=rabbitmq-server.elkr.rancher.internal/g" \
       -e "s/^LOG_RABBIT_PORT=5672/LOG_RABBIT_PORT=5672/g" \
       -e "s/^LOG_RABBIT_USER=log_rabbit/LOG_RABBIT_USER=ihub/g" \
       -e "s/^LOG_RABBIT_PASS=log_rabbit/LOG_RABBIT_PASS=\"8jm7JMBmCsqp\"/g" \
       -e "s/^LOG_RABBIT_PREFIX=log_rabbit_prefix/LOG_RABBIT_PREFIX=\"\"/g" \
        \
        -e "s/^LOG_EXTERNAL_REQUESTS=false/LOG_EXTERNAL_REQUESTS=true/g" \
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
       -e "s/^GAME_SESSION_STORAGE_TTL=900/GAME_SESSION_STORAGE_TTL=900/g" \
        \
       -e "s/^RABBITMQ_HOST=example.com/RABBITMQ_HOST=rabbitmq-server.elkr.rancher.internal/g" \
       -e "s/^RABBITMQ_PORT=5672/RABBITMQ_PORT=5672/g" \
       -e "s/^RABBITMQ_USER=user/RABBITMQ_USER=ihub/g" \
       -e "s/^RABBITMQ_PASS=pass/RABBITMQ_PASS=\"8jm7JMBmCsqp\"/g" \
       -e "s/^RABBITMQ_PREFIX=/RABBITMQ_PREFIX=\"\"/g" \
       -e "s/^RABBITMQ_MYSTERION_QUEUE=mysterion_transactions/RABBITMQ_MYSTERION_QUEUE=mysterion_transactions/g" \
        \
       -e "s/^API_MYSTERION_IS_ENABLED=false/API_MYSTERION_IS_ENABLED=false/g" \
       -e "s/^API_MYSTERION_HOST=example.com/API_MYSTERION_HOST=10.141.11.54/g" \
       -e "s/^API_MYSTERION_PORT=6666/API_MYSTERION_PORT=5000/g" \
       -e "s/^API_MYSTERION_ACTION=action/API_MYSTERION_ACTION=endpoint\//g" \
       -e "s/^API_MYSTERION_SID=sid/API_MYSTERION_SID=ihub/g" \
       -e "s/^API_MYSTERION_SKEY=skey/API_MYSTERION_SKEY=HeuHG0mjZTLkrdW9M2EjPy8O6GutHS7zgWy9Z6r4maJCombZNBh2AuK1tkwbjNih/g" \
        \
       -e "s/^DYNAMIC_SCHEDULER_API_LOGIN=login/DYNAMIC_SCHEDULER_API_LOGIN=dynamic_scheduler_api_login/g" \
       -e "s/^DYNAMIC_SCHEDULER_API_PASSWORD=\"password\"/DYNAMIC_SCHEDULER_API_PASSWORD=\"gBEWPkx4yGDCZj0P\"/g" \
        \
       ./.env
