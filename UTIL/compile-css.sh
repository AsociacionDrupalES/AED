#!/usr/bin/env bash

nvm use 8
cd web/themes/custom/aed_th
yarn install
yarn build
cd ../../../..
