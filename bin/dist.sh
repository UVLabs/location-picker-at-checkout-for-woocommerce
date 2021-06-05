#!/usr/bin/env bash

# Needs work

if [ -d "../dist" ]; then 
rm -Rf "../dist"; 
fi

if [ -d "../artifact" ]; then 
rm -Rf "../artifact"; 
fi

if [ ! -d "../dist" ]; then
  mkdir "../dist"
fi

if [ ! -d "../artifact" ]; then
  mkdir "../artifact"
fi

rsync -rc --exclude-from "../.distignore" "../" "../dist/lpac"

# cd dist
zip -r "../artifact/lpac.zip" "../dist/lpac"
# cd -