<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Cmf\Bundle\SimpleCmsBundle\DependencyInjection\CmfSimpleCmsExtension;
use Symfony\Component\DependencyInjection\Reference;

class CmfSimpleCmsExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return array(
            new CmfSimpleCmsExtension(),
        );
    }

    public function testLoadDefault()
    {
        $this->container->setParameter('kernel.bundles', array('CmfRoutingBundle' => true, 'SonataDoctrinePHPCRAdminBundle' => true, 'CmfMenuBundle' => true));

        $this->load(array(
            'persistence' => array(
                'phpcr' => array(
                    'enabled' => true,
                ),
            ),
        ));

        $this->assertContainerBuilderHasService('cmf_simple_cms.initializer', '%cmf_simple_cms.initializer.class%');
        $this->assertContainerBuilderHasService('cmf_simple_cms.persistence.phpcr.migrator.page', '%cmf_simple_cms.persistence.phpcr.migrator_page.class%');
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('cmf_simple_cms.persistence.phpcr.menu_provider', 'setManagerName', array(
            '%cmf_simple_cms.persistence.phpcr.manager_name%',
        ));
        $this->assertContainerBuilderHasService('cmf_simple_cms.persistence.phpcr.admin.page', '%cmf_simple_cms.persistence.phpcr.admin.page.class%');
    }

    public function testLoadMinimal()
    {
        $this->load(array(
            'persistence' => array(
                'phpcr' => array(
                    'enabled' => true,
                    'use_sonata_admin' => false,
                ),
            ),
            'use_menu' => false,
        ));

        $this->assertFalse($this->container->has('cmf_simple_cms.persistence.phpcr.admin.page'));
        $this->assertFalse($this->container->has('cmf_simple_cms.persistence.phpcr.menu_provider'));
    }
}
