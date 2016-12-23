#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

openssl enc -d -aes-256-cbc -salt -a -in $DIR/install-rancher.sh.enc -out $DIR/install-rancher.sh -k $DEPLOY_PROD_PASSPHRASE
