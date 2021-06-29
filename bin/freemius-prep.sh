#!/usr/bin/env bash

# Prepare plugin for uploading to wp.org.

# Start fresh
rm -rf dist
rm -rf artifact
rm lpac.zip

# Make our directories
mkdir -p dist
mkdir -p artifact

# Remove dev dependencies
composer install --no-dev

# Sync SVN
# rsync -acvP --delete --exclude-from=".distignore" ./ "$LPAC_SVN"

# Sync dist folder
rsync -acvP --delete --exclude-from=".distignore" ./ "./dist"

#Change to our dist folder and zip to artifact folder
(cd dist && zip -r ../artifact/lpac.zip .)

# Re-add dev dependencies
composer install

