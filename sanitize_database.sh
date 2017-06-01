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
echo -e "${LBLUE} > Table user__field_social_links.${DEFAULT}"
drush sqlq "UPDATE user__field_social_links SET field_social_links_title = substring(MD5(RAND()), -8);"
drush sqlq "UPDATE user__field_social_links SET field_social_links_uri = concat('https://www.drupal.org/', RAND());"

echo -e "${LBLUE} > Table url_alias.${DEFAULT}"
drush sqlq "UPDATE url_alias SET alias = concat('/user/', RAND()) where source LIKE '%user%'"

echo -e "${LBLUE} > Table user__field_name.${DEFAULT}"
drush sqlq "UPDATE user__field_name SET field_name_value = concat('Name ', RAND())"

echo -e "${LBLUE} > Table users_field_data.${DEFAULT}"
drush sqlq "UPDATE users_field_data SET name = concat('u', uid) WHERE uid NOT IN (0,1)"
drush sqlq "UPDATE users_field_data SET mail = concat(uid, '@drupal.es') WHERE uid NOT IN (0,1)"
drush sqlq "UPDATE users_field_data SET init = concat(uid, '@drupal.es') WHERE uid NOT IN (0,1)"
