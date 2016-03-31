Changelog
=========

1.3.0
-----

1.3.0-RC1
---------

* **2015-08-31**: Added IvoryCKEditorBundle integration and added the
  `ivory_ckeditor` settings.
* **2015-10-31**: Calling `getCreateDate()`, `getAddLocalePattern()` or
  `setAddLocalePattern()` now results in a deprecation notice. The methods are
  deprecated since 1.1 and will be removed in 2.0.

1.2.0-RC1
---------

* **2014-06-06**: Updated to PSR-4 autoloading

1.1.1
-----

* **2014-05-26**: Page now provides a convenience method that returns the UUID
  of the document if it has one.

1.1.0
-----

Release 1.1.0

1.1.0-RC2
---------

* **2014-04-15**: Page now also provides the additionalInfoBlock child. Removed
  the createDate as its redundant with the publishStartDate.

* **2014-04-11**: drop Symfony 2.2 compatibility

1.1.0-RC1
---------

* **2014-04-01**: Refactored the RoutingBundle to provide all routing
  features needed by SimpleCmsBundle.
  * The dynamic router of RoutingBundle needs to be active. The
    SimpleCmsBundle automatically does this for you.
  * The configuration for document to route/template now all happens under
    cmf_routing.dynamic and the route enhancers also apply to simplecms Pages.
    You can configure additional base paths where to look for routes in the
    cmf_routing.persistence.phpcr.base_routepaths field.
  * The configuration for locales is not needed anymore. Configuring it on the
    cmf_routing is enough.
  * The options for the format pattern, trailing slash and locale pattern are
    now moved into the route "options" and the Page Document now takes an
    array of options in the constructor instead of boolean flags.

* **2013-11-14**: The Page now supports the menu options that make sense for a
  page: display, displayChildren, attributes, children|link|labelAttributes.
  Note that those only affect the menu rendering, not anything else.

1.0.0-RC3
---------

* **2013-08-31**: The multilang.locales configuration was added back as we do
  need to care about locales and not depend on them when no locales are used.

1.0.0-RC1
---------

* **2013-08-10**: Removed "tags" property, see: https://github.com/symfony-cmf/SimpleCmsBundle/issues/53
* **2013-08-08**:
  * Seperate Multilang document now incorporated in single document
  * `multilang.locales` config removed (use cmf_core.multilang.locales)
  * `DataFixtures/LoadCmsData` renamed and moved to DataFixtures/Phpcr
  * PHPCR documents moved from Document to Doctrine/Phpcr

Migration instructions:

1. Set "addLocalePattern" flag to existing MultilangPage documents:

````bash
    ./app/console doctrine:phpcr:nodes:update --query="SELECT * FROM [nt:unstructured] WHERE phpcr:class = \"Symfony\\Cmf\\Bundle\\SimpleCmsBundle\\Document\\MultilangPage\"" --apply-closure="\$node->setProperty('addLocalePattern', true);"
````

2. Rename classes:

````bash
    export CMFNS="Symfony\\Cmf\\Bundle\\SimpleCmsBundle"
    ./app/console doctrine:phpcr:document:migrate-class \
       $CMFNS"\\Document\\MultilangPage" \
       $CMFNS"\\Doctrine\\Phpcr\\Page"
    ./app/console doctrine:phpcr:document:migrate-class \
       $CMFNS"\\Document\\MultilangRedirectRoute" \
       $CMFNS"\\Doctrine\\Phpcr\\MultilangRedirectRoute"
    ./app/console doctrine:phpcr:document:migrate-class \
       $CMFNS"\\Document\\MultilangRoute" \
       $CMFNS"\\Doctrine\\Phpcr\\MultilangRoute"
    ./app/console doctrine:phpcr:document:migrate-class \
       $CMFNS"\\Document\\MultilangRouteProvider" \
       $CMFNS"\\Doctrine\\Phpcr\\MultilangRouteProvider"
    ./app/console doctrine:phpcr:document:migrate-class \
       $CMFNS"\\Document\\Page" \
       $CMFNS"\\Doctrine\\Phpcr\\Page"
````

* **2013-08-04**: Changed name of Sonata route names / patterns - now /admin/cmf/simplecms/foo instead of /admin/bundle/simplecms/foo

1.0.0-beta3
-----------

* **2013-07-31**: Updated to work with latest versions of all dependencies
