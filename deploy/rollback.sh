#!/usr/bin/env bash

set -Eeuo pipefail

DEPLOY_PATH="${1:-}"
TARGET_RELEASE="${2:-previous}"
HEALTHCHECK_URL="${3:-}"
PHP_BIN="${PHP_BIN:-/usr/bin/php8.4}"

if [[ -z "$DEPLOY_PATH" || "$DEPLOY_PATH" != /* || "$DEPLOY_PATH" == "/" ]]; then
    echo "The deploy path must be an absolute path other than /." >&2
    exit 1
fi

SCRIPT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
"$SCRIPT_PATH/preflight.sh" "$DEPLOY_PATH"

if [[ "$TARGET_RELEASE" == "previous" ]]; then
    if [[ ! -L "$DEPLOY_PATH/previous" ]]; then
        echo "Previous release is not available." >&2
        exit 1
    fi
    TARGET_PATH="$(readlink -f "$DEPLOY_PATH/previous")"
elif [[ "$TARGET_RELEASE" =~ ^[A-Za-z0-9._-]+$ ]]; then
    TARGET_PATH="$DEPLOY_PATH/releases/$TARGET_RELEASE"
else
    echo "Invalid rollback target." >&2
    exit 1
fi

if [[ ! -d "$TARGET_PATH" || "$TARGET_PATH" != "$DEPLOY_PATH/releases/"* ]]; then
    echo "Rollback target is outside the release directory." >&2
    exit 1
fi

exec 9>"$DEPLOY_PATH/.deploy.lock"
if ! flock -n 9; then
    echo "Another deployment is already running." >&2
    exit 1
fi

CURRENT_LINK="$DEPLOY_PATH/current"
if [[ ! -L "$CURRENT_LINK" ]]; then
    echo "Current release symlink is missing." >&2
    exit 1
fi

current_release="$(readlink -f "$CURRENT_LINK")"
switched=0

recover_on_error() {
    exit_code=$?
    trap - ERR

    if (( switched == 1 )) && [[ -d "$current_release" ]]; then
        recovery_link="$DEPLOY_PATH/.rollback-recovery"
        ln -s "$current_release" "$recovery_link"
        mv -Tf "$recovery_link" "$CURRENT_LINK"
        "$PHP_BIN" "$current_release/artisan" reload || true
    fi

    "$PHP_BIN" "$CURRENT_LINK/artisan" up || true
    echo "Rollback failed; the original release was restored when possible." >&2
    exit "$exit_code"
}
trap recover_on_error ERR

"$PHP_BIN" "$current_release/artisan" down --retry=60

rollback_link="$DEPLOY_PATH/.rollback-$(basename "$TARGET_PATH")"
ln -s "$TARGET_PATH" "$rollback_link"
mv -Tf "$rollback_link" "$CURRENT_LINK"
switched=1

previous_link="$DEPLOY_PATH/.previous-rollback"
ln -s "$current_release" "$previous_link"
mv -Tf "$previous_link" "$DEPLOY_PATH/previous"

cd "$TARGET_PATH"
"$PHP_BIN" artisan reload
"$PHP_BIN" artisan up

if [[ -n "$HEALTHCHECK_URL" ]]; then
    curl --fail --silent --show-error --retry 5 --retry-delay 2 "$HEALTHCHECK_URL" >/dev/null
fi

trap - ERR
echo "Rolled back to $(basename "$TARGET_PATH"). Database migrations were not reverted."
