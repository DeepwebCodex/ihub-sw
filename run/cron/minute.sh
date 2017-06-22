#!/usr/bin/env bash

echo "--- Start --- $(date) $(ls -1 | wc -l)"

/usr/local/bin/php /www/artisan schedule:fetch-cron

echo "--- Finish -- $(date) $(ls -1 | wc -l)"