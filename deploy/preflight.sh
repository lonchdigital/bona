#!/usr/bin/env bash

set -Eeuo pipefail

DEPLOY_PATH="${1:-}"
PHP_BIN="${PHP_BIN:-/usr/bin/php8.4}"

if [[ -z "$DEPLOY_PATH" || "$DEPLOY_PATH" != /* || "$DEPLOY_PATH" == "/" ]]; then
    echo "Usage: PHP_BIN=/usr/bin/php8.4 $0 /absolute/deploy/path" >&2
    exit 1
fi

if [[ ! -x "$PHP_BIN" ]]; then
    echo "PHP binary is not executable: $PHP_BIN" >&2
    exit 1
fi

for command_name in curl flock tar sha256sum; do
    if ! command -v "$command_name" >/dev/null 2>&1; then
        echo "Required command is missing: $command_name" >&2
        exit 1
    fi
done

if ! "$PHP_BIN" -r 'exit(version_compare(PHP_VERSION, "8.4.0", ">=") ? 0 : 1);'; then
    echo "PHP 8.4 or newer is required." >&2
    exit 1
fi

required_extensions=(bcmath curl dom fileinfo gd intl mbstring openssl pdo_mysql xml zip)
missing_extensions=()

for extension_name in "${required_extensions[@]}"; do
    if ! "$PHP_BIN" -m | grep -Fxq "$extension_name"; then
        missing_extensions+=("$extension_name")
    fi
done

if (( ${#missing_extensions[@]} > 0 )); then
    echo "Missing PHP extensions: ${missing_extensions[*]}" >&2
    exit 1
fi

if [[ ! -f "$DEPLOY_PATH/shared/.env" ]]; then
    echo "Shared environment file is missing: $DEPLOY_PATH/shared/.env" >&2
    exit 1
fi

if [[ ! -d "$DEPLOY_PATH/shared/storage" || ! -w "$DEPLOY_PATH/shared/storage" ]]; then
    echo "Shared storage must exist and be writable: $DEPLOY_PATH/shared/storage" >&2
    exit 1
fi

if [[ -e "$DEPLOY_PATH/current" && ! -L "$DEPLOY_PATH/current" ]]; then
    echo "Current path exists but is not a symlink: $DEPLOY_PATH/current" >&2
    exit 1
fi

echo "Production preflight passed with $($PHP_BIN -r 'echo PHP_VERSION;')."
