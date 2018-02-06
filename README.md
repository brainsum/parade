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


The required geocoder_autocomplete has a missing schema.
See: https://www.drupal.org/node/2858115
Use this patch for the 8.x-1.0 version OR use the 8.x-dev version:
https://www.drupal.org/files/issues/missing-schema-2858115-2.patch

There is a bug in geocoder 8.x-2.0 version that causes an error if geocoder
module has not been configured.
Use this patch for the 8.x-2.0 version OR use the 8.x-2.0-beta2 version:
https://www.drupal.org/files/issues/null_third_arg_error_calling_geocode_2937492-11.diff

Add them to the extra section of the composer.json file:

       "patches": {
           "drupal/geocoder_autocomplete": {
               "fix missing schema": "https://www.drupal.org/files/issues/missing-schema-2858115-2.patch"
           },
           "drupal/geocoder": {
               "fix null third arg error" : "https://www.drupal.org/files/issues/null_third_arg_error_calling_geocode_2937492-11.diff"
           }
       }

### Without composer
@todo


## CONFIGURATION

@todo

## ISSUES

@todo
