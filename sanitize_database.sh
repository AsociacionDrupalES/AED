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
drush sqlsan -y --sanitize-password=12345 --sanitize-email=dr%nid@drupal.es

# Sanitize custom fields
echo -e "${LBLUE} > Tables with personal data like field_data_field_*.${DEFAULT}"
drush sqlq "UPDATE field_data_field_nombre SET field_nombre_value = (SELECT LEFT(UUID(), 8));"
drush sqlq "UPDATE field_revision_field_nombre SET field_nombre_value = (SELECT LEFT(UUID(), 8));"

drush sqlq "UPDATE field_data_field_name SET field_name_value = (SELECT LEFT(UUID(), 8));"
drush sqlq "UPDATE field_revision_field_name SET field_name_value = (SELECT LEFT(UUID(), 8));"

drush sqlq "UPDATE field_data_field_apellidos SET field_apellidos_value = (SELECT LEFT(UUID(), 8));"
drush sqlq "UPDATE field_revision_field_apellidos SET field_apellidos_value = (SELECT LEFT(UUID(), 8));"

drush sqlq "UPDATE field_data_field_email SET field_email_email = 'x@x.com';"
drush sqlq "UPDATE field_revision_field_email SET field_email_email = 'x@x.com';"

drush sqlq "UPDATE field_data_field_pagina_web SET field_pagina_web_value = concat('https://www.google.es/', RAND());"
drush sqlq "UPDATE field_revision_field_pagina_web SET field_pagina_web_value = concat('https://www.google.es/', RAND());"

drush sqlq "UPDATE field_data_field_perfil_facebook SET field_perfil_facebook_value = concat('https://www.facebook.com/', RAND());"
drush sqlq "UPDATE field_revision_field_perfil_facebook SET field_perfil_facebook_value = concat('https://www.facebook.com/', RAND());"

drush sqlq "UPDATE field_data_field_perfil_twitter SET field_perfil_twitter_value = concat('https://twitter.com/', RAND());"
drush sqlq "UPDATE field_revision_field_perfil_twitter SET field_perfil_twitter_value = concat('https://twitter.com/', RAND());"

drush sqlq "UPDATE field_data_field_perfil_linkedin SET field_perfil_linkedin_value = concat('https://linkedin.com/', RAND());"
drush sqlq "UPDATE field_revision_field_perfil_linkedin SET field_perfil_linkedin_value = concat('https://linkedin.com/', RAND());"

drush sqlq "UPDATE field_data_field_perfil_google_plus SET field_perfil_google_plus_value = concat('https://plus.google.com/', RAND());"
drush sqlq "UPDATE field_revision_field_perfil_google_plus SET field_perfil_google_plus_value = concat('https://plus.google.com/', RAND());"

drush sqlq "UPDATE field_data_field_perfil_en_drupal_org SET field_perfil_en_drupal_org_value = concat('https://www.drupal.org/', RAND());"
drush sqlq "UPDATE field_revision_field_perfil_en_drupal_org SET field_perfil_en_drupal_org_value = concat('https://www.drupal.org/', RAND());"

drush sqlq "UPDATE field_data_field_mensaje SET field_mensaje_value = concat('This is the message: ', RAND())"
drush sqlq "UPDATE field_revision_field_mensaje SET field_mensaje_value = concat('This is the message: ', RAND())"

echo -e "${LBLUE} > Table user__field_social_links.${DEFAULT}"
drush sqlq "UPDATE user__field_social_links SET field_social_links_title = substring(MD5(RAND()), -8);"
drush sqlq "UPDATE user__field_social_links SET field_social_links_uri = concat('https://www.drupal.org/', RAND());"

echo -e "${LBLUE} > Table users_field_data.${DEFAULT}"
drush sqlq "UPDATE users_field_data SET name = concat('u', uid) WHERE uid NOT IN (0,1)"
drush sqlq "UPDATE users_field_data SET mail = concat(uid, '@drupal.es') WHERE uid NOT IN (0,1)"
drush sqlq "UPDATE users_field_data SET init = concat(uid, '@drupal.es') WHERE uid NOT IN (0,1)"

echo -e "${LBLUE} > Table url_alias.${DEFAULT}"
drush sqlq "UPDATE url_alias SET alias = concat('/user/', RAND()) where source LIKE '%user%'"

echo -e "${LBLUE} > Truncate commerce tables.${DEFAULT}"
drush sqlq "TRUNCATE TABLE commerce_addressbook_defaults;"
drush sqlq "TRUNCATE TABLE commerce_customer_profile;"
drush sqlq "TRUNCATE TABLE commerce_customer_profile_revision;"
drush sqlq "TRUNCATE TABLE commerce_line_item;"
drush sqlq "TRUNCATE TABLE commerce_order;"
drush sqlq "TRUNCATE TABLE commerce_order_revision;"
drush sqlq "TRUNCATE TABLE commerce_payment_transaction;"
drush sqlq "TRUNCATE TABLE commerce_payment_transaction_revision;"
drush sqlq "TRUNCATE TABLE commerce_paypal_ipn;"
drush sqlq "TRUNCATE TABLE commerce_recurring_paypal;"

# Additional core tables sanitization
echo -e "${LBLUE} > Truncate search tables.${DEFAULT}"
drush sqlq "TRUNCATE TABLE search_index;"
drush sqlq "TRUNCATE TABLE search_total;"

echo -e "${LBLUE} > Truncate cache_render table.${DEFAULT}"
drush sqlq "TRUNCATE TABLE cache_render;"

echo -e "${LBLUE} > Truncate watchdog table.${DEFAULT}"
drush sqlq "TRUNCATE TABLE watchdog;"

echo -e "${LBLUE} > Sanitize config variables.${DEFAULT}"
drush -y config-set system.site mail x@x.com
drush -y config-set update.settings notification.emails.0 x@x.com
drush -y config-set contact.form.feedback recipients x@x.com

echo -e "${LBLUE} > Clear the cache ;)${DEFAULT}"
drush cr
