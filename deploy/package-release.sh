#!/usr/bin/env bash

set -Eeuo pipefail

ROOT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUTPUT_PATH="${1:-$ROOT_PATH/.release}"
RELEASE_ID="${2:-$(git -C "$ROOT_PATH" rev-parse --short=12 HEAD)}"

if [[ ! "$RELEASE_ID" =~ ^[A-Za-z0-9._-]+$ ]]; then
    echo "Release id may contain only letters, numbers, dots, underscores, and dashes." >&2
    exit 1
fi

mkdir -p "$OUTPUT_PATH"
STAGE_PATH="$(mktemp -d "$OUTPUT_PATH/.stage.XXXXXX")"

cleanup() {
    rm -rf -- "$STAGE_PATH"
}
trap cleanup EXIT

if ! git -C "$ROOT_PATH" diff --quiet || ! git -C "$ROOT_PATH" diff --cached --quiet; then
    echo "Tracked files must be committed before packaging a release." >&2
    exit 1
fi

git -C "$ROOT_PATH" archive --format=tar HEAD | tar -xf - -C "$STAGE_PATH"
rsync -a "$ROOT_PATH/vendor/" "$STAGE_PATH/vendor/"
rsync -a "$ROOT_PATH/public/build/" "$STAGE_PATH/public/build/"

printf '%s\n' "$(git -C "$ROOT_PATH" rev-parse HEAD)" > "$STAGE_PATH/REVISION"

for required_path in artisan composer.lock public/build/manifest.json vendor/autoload.php; do
    if [[ ! -e "$STAGE_PATH/$required_path" ]]; then
        echo "Release is missing required path: $required_path" >&2
        exit 1
    fi
done

ARCHIVE_PATH="$OUTPUT_PATH/bona-$RELEASE_ID.tar.gz"
tar -czf "$ARCHIVE_PATH" -C "$STAGE_PATH" .

if command -v sha256sum >/dev/null 2>&1; then
    sha256sum "$ARCHIVE_PATH" > "$ARCHIVE_PATH.sha256"
else
    shasum -a 256 "$ARCHIVE_PATH" > "$ARCHIVE_PATH.sha256"
fi

echo "$ARCHIVE_PATH"
