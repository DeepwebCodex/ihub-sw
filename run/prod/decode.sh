#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

openssl enc -d -aes-256-cbc -salt -a -in $DIR/make-configs.sh.enc -out $DIR/make-configs.sh -k $DECODE_PROD_CONFIGS_PASSPHRASE
