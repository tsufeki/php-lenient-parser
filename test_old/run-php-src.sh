#!/bin/bash

DIR="$(dirname "$0")/.."
ARCHIVE_URL='https://github.com/php/php-src/archive/php-7.1.0.tar.gz'
ARCHIVE="$DIR/php-7.1.0.tar.gz"
UNPACK_DIR="$DIR/data/php-src"

if ! [[ -a "$ARCHIVE" ]]; then
    echo 'Downloading PHP source tests...'
    wget -q "$ARCHIVE_URL" -O "$ARCHIVE"
fi
mkdir -p "$UNPACK_DIR"
tar -xzf "$ARCHIVE" -C "$UNPACK_DIR" --strip-components=1
echo 'Running tests...'
php "$DIR/test_old/run.php" --verbose --no-progress PHP7 "$UNPACK_DIR"

