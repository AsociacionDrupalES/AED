#!/usr/bin/env bash

nvm install 8
cd web/themes/custom/aed_th
# rm -rf node_modules
npm rebuild node-sass
yarn install
yarn build
cd ../../../..
