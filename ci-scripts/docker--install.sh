#!/bin/sh

# Install composer dependencies and the site.
composer config repositories.leaflet '{"type": "package", "package": {"name": "leaflet/leaflet", "version": "v0.7.7", "type": "drupal-library", "dist": {"url": "https://github.com/Leaflet/Leaflet/archive/v0.7.7.zip", "type": "zip" }}}' \
  && composer config extra.patches-file "composer.patches.json" \
  && composer config discard-changes true \
  && composer require drupal/parade:2.x-dev drupal/coder \
  && composer install -n \
  && vendor/bin/phpcs --config-set installed_paths /var/www/html/vendor/drupal/coder/coder_sniffer \
  && cd web \
  && drush site-install --site-name="Test" --account-pass=123 --db-url=mysql://drupal:drupal@mariadb/drupal standard -y \
  && drush en parade_demo -y \
  && drush cr
