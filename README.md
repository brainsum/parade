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

### MULTIDOMAIN SETTINGS (opcional)

You need to use domain module to set these settings, however if you don't need multidomain
settings you don't need to install domain module. If you install domain module we detect this
and some optional settings will be appear.

Benefits: 
- You will be able to set front page per domain from UI.
- You will be able to set GA account per domain from UI.

#### Front page per domain
If you want to use this, you need to install the domain, domain_access and domain_config
modules, than make sure at /admin/config/domain/domain_access the "Move Domain Access fields
to advanced node settings." is chacked. You will find the front page settings at the node 
edit pages right under "DOMAIN SETTINGS".

#### GA account per domain
To use this, you need to install the domain, domain_config and google_analytics modules. 
After you did it, go to /admin/config/system/google-analytics page and you will find a new 
settings tab called "DOMAIN SPECIFIC SETTINGS". Under this tab, you will be able to:
- turn off GA for a specific domain (empty text field, unchecked "Use value from General 
settings" checkbox)
- overwrite GA account ID for a specific domain (filled text field, unchecked "Use value 
from General settings" checkbox)
- let it use the default value (from General Settings tab (empty text field, checked "Use 
value from General settings" checkbox)) - when you install the module this is the default.

## ISSUES

@todo

