#!/usr/bin/env bash

set -eo pipefail

# @link https://github.com/docker-library/healthcheck/blob/master/mysql/docker-healthcheck

if [ "$MYSQL_RANDOM_ROOT_PASSWORD" ] && [ -z "$MYSQL_USER" ] && [ -z "$MYSQL_PASSWORD" ]; then
    # there's no way we can guess what the random MySQL password was
    echo >&2 'healthcheck error: cannot determine random root password (and MYSQL_USER and MYSQL_PASSWORD were not set)'
    exit 0
fi

# force mysql to not use the local "mysqld.sock" (test "external" conductibility)
host="127.0.0.1"
user="${MYSQL_USER:-root}"

if [ -z "$MYSQL_PASSWORD" ] || [ -z "$MYSQL_ROOT_PASSWORD" ]; then
    # use password only if exists
    export MYSQL_PWD="${MYSQL_PASSWORD:-$MYSQL_ROOT_PASSWORD}"
fi

args=(
    -h"$host"
    -u"$user"
    --silent
)

if select="$(echo 'SELECT 1' | mysql "${args[@]}")" && [ "$select" = '1' ]; then
    exit 0
fi

exit 1