Changelog
=========

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
