#!/usr/bin/env bash

nvm install 22
cd web/themes/custom/aed_th
# rm -rf node_modules
# npm rebuild node-sass
yarn install
yarn build
cd ../../../..

drush cr
