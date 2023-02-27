#!/bin/sh

rm -fR assets
mkdir assets

git clone --depth 1 https://github.com/unicode-org/cldr-json.git assets/cldr
