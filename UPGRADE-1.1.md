UPGRADE FROM 1.0 TO 1.1
=======================

Removed all custom routing service as RoutingBundle is now flexible enough.
  * Move all routing configuration from cmf_simple_cms to cmf_routing.dynamic
  * The configuration for locales is not needed anymore. Configuring it on the
    cmf_routing is enough.
  * The options for the format pattern, trailing slash and locale pattern are
    now moved into the route "options" and the Page Document now takes an
    array of options in the constructor instead of boolean flags.

The Page document has no `createDate` field anymore. Use the publishStartDate
for this purpose. If you where using the createDate and want to keep that
information, you can move it to the publishStartDate with the following query:

    $ php app/console doctrine:phpcr:nodes:update \
        --query="SELECT * FROM [nt:unstructured] WHERE [phpcr:class] = \"Symfony\\Cmf\\Bundle\\SimpleCmsBundle\\Doctrine\\Phpcr\\Page\"" \
        --apply-closure="if (!\$node->getPublishStartDate()) \$node->setProperty('publishStartDate', \$node->getPropertyValue('createDate'));"
