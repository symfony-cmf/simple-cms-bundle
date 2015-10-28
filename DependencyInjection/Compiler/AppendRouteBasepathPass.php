<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Appends the basepath for the SimpleCms to the RoutingBundle route basepaths.
 *
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
class AppendRouteBasepathPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('cmf_routing.dynamic.persistence.phpcr.route_basepaths')) {
            return;
        }

        $routeBasepaths = $container->getParameter('cmf_routing.dynamic.persistence.phpcr.route_basepaths');
        $routeBasepaths[] = $container->getParameter('cmf_simple_cms.persistence.phpcr.basepath');

        $container->setParameter('cmf_routing.dynamic.persistence.phpcr.route_basepaths', array_unique($routeBasepaths));
    }
}
