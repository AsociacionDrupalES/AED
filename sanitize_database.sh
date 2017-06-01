#!/usr/bin/env bash

# Restore to default colours
DEFAULT='\033[0m'
# Bold blue color
LBLUE='\033[01;34m'

echo
echo -e "${LBLUE} > You're about to modify your database.${DEFAULT}"
read -p "Are you sure (y/n)? "

if [[ ! $REPLY =~ ^[Yy]$ ]]
then
    exit 1
fi

# Default sanitization for database.
echo
echo -e "${LBLUE} > Sanitizing core fields.${DEFAULT}"
drush sqlsan --sanitize-password=12345 --sanitize-email=dr%nid@drupal.es

echo
echo -e "${LBLUE} > Updating non-core fields.${DEFAULT}"
drush sqlq "UPDATE users SET mail = substring(MD5(RAND()), -8);"

