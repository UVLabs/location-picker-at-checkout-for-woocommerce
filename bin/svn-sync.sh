#!/usr/bin/env bash

mkdir -p "dist"
mkdir -p "artifact"

rsync -acvP --delete --exclude-from=".distignore" ./ "$LPAC_SVN"