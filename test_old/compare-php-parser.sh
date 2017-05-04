#!/bin/bash

DIR="$(dirname "$0")/.."
LP="$DIR/bin/php-lenient-parse"
PP="$DIR/vendor/bin/php-parse"
OPTS=
DIFFOPTS=-U10

find "$@" -name '*.php' -type f -print0 | xargs -0 -n1 -I'{}' \
    bash -c "diff $DIFFOPTS <(\"$PP\" $OPTS \"{}\") <(\"$LP\" $OPTS \"{}\") || echo -ne \"^ {}\\n\\n\""
