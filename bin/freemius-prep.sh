#!/usr/bin/env bash

# Prepare plugin for uploading to wp.org.

# Start fresh
rm -rf dist
rm -rf artifact
rm lpac.zip

# Make our directories
mkdir -p dist
mkdir -p artifact

# Remove vendor folder so we can redownload without dev dependencies.
rm -rf vendor

# Remove dev dependencies
composer install --no-dev
composer dumpautoload

# Remove the QR Code library font files from dist. They make the library huge.
rm "vendor/endroid/qr-code/assets/noto_sans.otf"
rm "vendor/endroid/qr-code/assets/open_sans.ttf"

# Run Prettier
npm run format

# Build our JS files with parcel
npm run build

# Sync dist folder
rsync -acvP --delete --exclude-from=".distignore" ./ "./dist"

#Change to our dist folder and zip to artifact folder
(cd dist && zip -r ../artifact/lpac.zip .)

# Delete dist folder
rm -rf dist

# Delete the QR code library so all its files can be added back when the composer install command runs
rm -r "vendor/endroid"

# Re-add dev dependencies
composer install
composer dumpautoload

