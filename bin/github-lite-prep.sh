#!/usr/bin/env bash

# # Prepare plugin for uploading to lite version on github.

# Start fresh
rm -rf dist
rm -rf artifact
rm lpac.zip

# Make our directories
mkdir -p dist
mkdir -p artifact

# Remove dev dependencies
composer install --no-dev
composer dumpautoload

# Run Prettier
npm run format

# Sync dist folder
rsync -acvP --delete --exclude-from=".distignore-github-lite" ./ "./dist"

#Change to our dist folder and zip to artifact folder
(cd dist && zip -r ../artifact/lpac.zip .)

# Re-add dev dependencies
composer install
composer dumpautoload

