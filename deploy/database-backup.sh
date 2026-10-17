#!/usr/bin/env bash

set -Eeuo pipefail

DEPLOY_PATH="${1:-}"
PHP_BIN="${PHP_BIN:-/usr/bin/php8.4}"

if [[ -z "$DEPLOY_PATH" || "$DEPLOY_PATH" != /* || "$DEPLOY_PATH" == "/" ]]; then
    echo "The deploy path must be an absolute path other than /." >&2
    exit 1
fi

CURRENT_PATH="$DEPLOY_PATH/current"

if [[ ! -L "$CURRENT_PATH" || ! -f "$CURRENT_PATH/bootstrap/app.php" ]]; then
    echo "The active Laravel release is unavailable." >&2
    exit 1
fi

if [[ ! -x "$PHP_BIN" ]]; then
    echo "The configured PHP binary is unavailable." >&2
    exit 1
fi

dump_binary="$(command -v mariadb-dump || command -v mysqldump || true)"
if [[ -z "$dump_binary" ]]; then
    echo "Neither mariadb-dump nor mysqldump is installed." >&2
    exit 1
fi

# Laravel resolves quoted dotenv values, config indirection, and cached config.
# escapeshellarg keeps every value safe when the assignments are evaluated by Bash.
database_exports="$($PHP_BIN -r '
    $root = $argv[1];
    require $root . "/vendor/autoload.php";
    $app = require $root . "/bootstrap/app.php";
    $app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();

    $default = (string) config("database.default");
    $config = (array) config("database.connections." . $default, []);

    if (!in_array($config["driver"] ?? null, ["mysql", "mariadb"], true)) {
        fwrite(STDERR, "The production database is not MySQL/MariaDB.\n");
        exit(2);
    }

    $values = [
        "BACKUP_DB_HOST" => $config["host"] ?? "127.0.0.1",
        "BACKUP_DB_PORT" => $config["port"] ?? "3306",
        "BACKUP_DB_NAME" => $config["database"] ?? "",
        "BACKUP_DB_USER" => $config["username"] ?? "",
        "BACKUP_DB_PASSWORD" => $config["password"] ?? "",
        "BACKUP_DB_SOCKET" => $config["unix_socket"] ?? "",
    ];

    foreach ($values as $name => $value) {
        printf("%s=%s\n", $name, escapeshellarg((string) $value));
    }
' "$CURRENT_PATH")"

eval "$database_exports"

if [[ -z "$BACKUP_DB_NAME" || -z "$BACKUP_DB_USER" ]]; then
    echo "The production database name or username is empty." >&2
    exit 1
fi

dump_args=(
    --host="$BACKUP_DB_HOST"
    --port="$BACKUP_DB_PORT"
    --user="$BACKUP_DB_USER"
    --single-transaction
    --quick
    --routines
    --events
    --triggers
    --hex-blob
    --default-character-set=utf8mb4
    --no-tablespaces
)

if [[ -n "$BACKUP_DB_SOCKET" ]]; then
    dump_args+=(--socket="$BACKUP_DB_SOCKET")
fi

export MYSQL_PWD="$BACKUP_DB_PASSWORD"
"$dump_binary" "${dump_args[@]}" -- "$BACKUP_DB_NAME" | gzip -1
