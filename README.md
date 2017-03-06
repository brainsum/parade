# README.md

Parade is a module to create pre-customized paragraphs in your content.
It's based on https://www.drupal.org/project/paragraphs .

## INSTALLATION
### With composer
You need to install the dependencies first. To do this,
look at the composer.json of the Parade module,
look for the 'repositories' key and copy the contents
of the 'repositories' to the composer.json of your own project.
After this, you just have to use "composer require drupal/parade"
to get the module and the dependencies,
and "drush en parade" to enable it in your site.

The required geocoder module has a typo in its schema. Use this patch:
"https://www.drupal.org/files/issues/2824802-geocoder-schema-fix-2.patch"

### Without composer
@todo


## CONFIGURATION

@todo

## ISSUES

@todo

