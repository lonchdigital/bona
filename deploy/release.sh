#!/usr/bin/env bash

set -Eeuo pipefail

DEPLOY_PATH="${1:-}"
RELEASE_ID="${2:-}"
ARCHIVE_PATH="${3:-}"
CHECKSUM_PATH="${4:-}"
HEALTHCHECK_URL="${5:-}"
PHP_BIN="${PHP_BIN:-/usr/bin/php8.4}"

if [[ -z "$DEPLOY_PATH" || "$DEPLOY_PATH" != /* || "$DEPLOY_PATH" == "/" ]]; then
    echo "The deploy path must be an absolute path other than /." >&2
    exit 1
fi

if [[ ! "$RELEASE_ID" =~ ^[A-Za-z0-9._-]+$ ]]; then
    echo "Invalid release id." >&2
    exit 1
fi

if [[ ! -f "$ARCHIVE_PATH" || ! -f "$CHECKSUM_PATH" ]]; then
    echo "Release archive or checksum is missing." >&2
    exit 1
fi

SCRIPT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
"$SCRIPT_PATH/preflight.sh" "$DEPLOY_PATH"

mkdir -p "$DEPLOY_PATH/releases" "$DEPLOY_PATH/incoming"
RELEASE_PATH="$DEPLOY_PATH/releases/$RELEASE_ID"
CURRENT_LINK="$DEPLOY_PATH/current"
PREVIOUS_LINK="$DEPLOY_PATH/previous"
SHARED_PATH="$DEPLOY_PATH/shared"
NEXT_LINK="$DEPLOY_PATH/.current-$RELEASE_ID"
LOCK_PATH="$DEPLOY_PATH/.deploy.lock"

exec 9>"$LOCK_PATH"
if ! flock -n 9; then
    echo "Another deployment is already running." >&2
    exit 1
fi

if [[ -e "$RELEASE_PATH" ]]; then
    echo "Release already exists: $RELEASE_PATH" >&2
    exit 1
fi

if [[ ! -L "$CURRENT_LINK" ]]; then
    echo "Atomic deployment requires an existing current symlink for rollback safety." >&2
    exit 1
fi

expected_checksum="$(awk 'NR == 1 { print $1 }' "$CHECKSUM_PATH")"
actual_checksum="$(sha256sum "$ARCHIVE_PATH" | awk '{ print $1 }')"

if [[ -z "$expected_checksum" || "$expected_checksum" != "$actual_checksum" ]]; then
    echo "Release checksum does not match." >&2
    exit 1
fi

previous_release="$(readlink -f "$CURRENT_LINK")"

mkdir "$RELEASE_PATH"
tar -xzf "$ARCHIVE_PATH" -C "$RELEASE_PATH"

# Keep deployments created from older artifacts safe as well: the release root
# must be traversable by the web-server process.
chmod 0755 "$RELEASE_PATH"

for required_path in artisan composer.lock public/build/manifest.json vendor/autoload.php REVISION; do
    if [[ ! -e "$RELEASE_PATH/$required_path" ]]; then
        echo "Extracted release is missing required path: $required_path" >&2
        exit 1
    fi
done

ln -s "$SHARED_PATH/.env" "$RELEASE_PATH/.env"

if [[ -e "$RELEASE_PATH/storage" || -L "$RELEASE_PATH/storage" ]]; then
    unexpected_storage_file="$(find "$RELEASE_PATH/storage" -mindepth 1 -type f ! -name '.gitignore' -print -quit)"
    unexpected_storage_link="$(find "$RELEASE_PATH/storage" -mindepth 1 -type l -print -quit)"

    if [[ -n "$unexpected_storage_file" || -n "$unexpected_storage_link" ]]; then
        echo "Release artifact contains unexpected runtime storage content." >&2
        exit 1
    fi

    rm -rf -- "$RELEASE_PATH/storage"
fi

ln -s "$SHARED_PATH/storage" "$RELEASE_PATH/storage"
mkdir -p "$SHARED_PATH/storage/app/public" \
    "$SHARED_PATH/storage/framework/cache/data" \
    "$SHARED_PATH/storage/framework/sessions" \
    "$SHARED_PATH/storage/framework/views" \
    "$SHARED_PATH/storage/logs" \
    "$RELEASE_PATH/bootstrap/cache"
ln -s "$SHARED_PATH/storage/app/public" "$RELEASE_PATH/public/storage"

maintenance_started=0
switched=0

recover_on_error() {
    exit_code=$?
    trap - ERR

    if (( switched == 1 )) && [[ -n "$previous_release" && -d "$previous_release" ]]; then
        rollback_link="$DEPLOY_PATH/.rollback-$RELEASE_ID"
        ln -s "$previous_release" "$rollback_link"
        mv -Tf "$rollback_link" "$CURRENT_LINK"
    fi

    if (( maintenance_started == 1 )) && [[ -L "$CURRENT_LINK" ]]; then
        "$PHP_BIN" "$CURRENT_LINK/artisan" up || true
    fi

    echo "Release failed. The extracted release was kept for inspection: $RELEASE_PATH" >&2
    exit "$exit_code"
}
trap recover_on_error ERR

"$PHP_BIN" "$previous_release/artisan" down --retry=60
maintenance_started=1

cd "$RELEASE_PATH"
"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan migrate --force
"$PHP_BIN" artisan optimize

ln -s "$RELEASE_PATH" "$NEXT_LINK"
mv -Tf "$NEXT_LINK" "$CURRENT_LINK"
switched=1

previous_next="$DEPLOY_PATH/.previous-$RELEASE_ID"
ln -s "$previous_release" "$previous_next"
mv -Tf "$previous_next" "$PREVIOUS_LINK"

"$PHP_BIN" artisan reload
"$PHP_BIN" artisan up
maintenance_started=0

if [[ -n "$HEALTHCHECK_URL" ]]; then
    curl --fail --silent --show-error --retry 5 --retry-delay 2 "$HEALTHCHECK_URL" >/dev/null
fi

# The extracted release is self-contained. Keeping its uploaded archive and
# checksum after a successful health check only wastes disk space.
rm -f -- "$ARCHIVE_PATH" "$CHECKSUM_PATH"

trap - ERR
echo "Release $RELEASE_ID is active."
