<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Tests\Unit\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Cmf\Bundle\SimpleCmsBundle\DependencyInjection\Compiler\AppendRouteBasepathPass;

class AppendRouteBasepathPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AppendRouteBasepathPass());
    }

    public function testAppendsSimpleCmsBasepathToCmfRouting()
    {
        $this->setParameter('cmf_routing.dynamic.persistence.phpcr.route_basepaths', array('/cms/routes'));
        $this->setParameter('cmf_simple_cms.persistence.phpcr.basepath', '/cms/simple');

        $this->compile();

        $this->assertContainerBuilderHasParameter('cmf_routing.dynamic.persistence.phpcr.route_basepaths', array('/cms/routes', '/cms/simple'));
    }
}
