# README.md

Parade is a module to create pre-customized paragraphs in your content.
It's based on https://www.drupal.org/project/paragraphs .

## INSTALLATION
### With composer
You need to add the following repositories to your composer.json:

    "drupal": {
        "type": "composer",
        "url": "https://packages.drupal.org/8"
    },
    "leaflet": {
        "type": "package",
        "package": {
            "name": "leaflet/leaflet",
            "version": "v0.7.7",
            "type": "drupal-library",
            "dist": {
                "url": "https://github.com/Leaflet/Leaflet/archive/v0.7.7.zip",
                "type": "zip"
            }
        }
    }

Composer can't resolve repositories of the dependencies, that's why you have to use this workaround.
After this, you just have to use "composer require drupal/parade"
to get the module and the dependencies,
and "drush en parade" to enable it in your site.

The required geocoder module has a typo in its schema.
See: https://www.drupal.org/node/2824802
Use this patch: https://www.drupal.org/files/issues/2824802-geocoder-schema-fix-2.patch

The required geocoder_autocomplete has a missing schema.
See: https://www.drupal.org/node/2858115
Use this patch: https://www.drupal.org/files/issues/missing-schema-2858115-2.patch

### Without composer
@todo


## CONFIGURATION

@todo

## ISSUES

@todo

