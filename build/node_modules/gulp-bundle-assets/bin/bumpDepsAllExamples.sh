#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
for path in ${DIR}/../examples/*/; do
    echo "$path"
    (cd "$path" && npm-check-updates -u && rm -r node_modules && npm install)
done