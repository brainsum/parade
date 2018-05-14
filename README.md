[![Build Status](https://travis-ci.org/brainsum/parade.svg?branch=8.x-2.x)](https://travis-ci.org/brainsum/parade)

# README.md

Parade is a module to create one page sites from pre-customized paragraphs in your content.
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

Composer can't resolve repositories of the dependencies, that's why you have to
use this workaround. After this, you just have to use "composer require
drupal/parade" to get the module and the dependencies, and "drush en parade" to
enable it in your site.

The required geocoder_field (submodule of geocoder) has a missing schema.
See: https://www.drupal.org/project/geocoder/issues/2954070 and
https://www.drupal.org/project/geocoder/issues/2971970
Use 8.x-2.0-beta3 version and mentioned patches.

The required geocoder_autocomplete has a missing schema.
See: https://www.drupal.org/node/2858115
Use this patch for the 8.x-1.0 version OR use the 8.x-dev version:
https://www.drupal.org/files/issues/missing-schema-2858115-2.patch

There are field groups compatibility issues with field layout regarding
field_group and paragraphs module.

See: https://www.drupal.org/project/field_group/issues/2878359
Use the ~3.0 version of field_group and patch:
https://www.drupal.org/files/issues/null_third_arg_error_calling_geocode_2937492-11.diff

See: https://www.drupal.org/node/2907094
Use the ~1.0 version of paragraphs and patch:
https://www.drupal.org/files/issues/2907094_7_field_group_support.patch

Add them to the extra section of the composer.json file:

       "patches": {
            "drupal/field_group": {
                "Field groups are not compatible with field layout": "https://www.drupal.org/files/issues/field-layout-support-2878359-36.patch"
            },
            "drupal/paragraphs": {
                "Fix support for field_group module in combination with field_layout module patch": "https://www.drupal.org/files/issues/2907094_7_field_group_support.patch"
            "drupal/geocoder": {
                "geocoder_field schema is not up to date": "https://www.drupal.org/files/issues/2018-03-18/2954070-2.patch",
                "Invalid geocoder_field schema": "https://www.drupal.org/files/issues/2018-05-14/invalid-geocoder_field-schema-2971970-5-D8.patch"
            },
           "drupal/geocoder_autocomplete": {
               "fix missing schema": "https://www.drupal.org/files/issues/missing-schema-2858115-2.patch"
           }
       }

### Without composer
@todo


## CONFIGURATION

@todo

## ISSUES

@todo
