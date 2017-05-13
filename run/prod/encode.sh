#!/usr/bin/env bash

echo "Enter passphrase:"
read DEPLOY_PASSPHRASE

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

openssl enc -aes-256-cbc -salt -a -in $DIR/make-configs.sh -out $DIR/make-configs.sh.enc -k $DEPLOY_PASSPHRASE