<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Tests\Unit\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Cmf\Bundle\SimpleCmsBundle\DependencyInjection\CmfSimpleCmsExtension;

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
        $this->setParameter('kernel.bundles', array('CmfRoutingBundle' => true, 'SonataDoctrinePHPCRAdminBundle' => true, 'CmfMenuBundle' => true, 'IvoryCKEditorBundle' => true));

        $this->load(array(
            'persistence' => array(
                'phpcr' => array(
                    'enabled' => true,
                ),
            ),
        ));

        $this->assertContainerBuilderHasService('cmf_simple_cms.initializer', 'Symfony\Cmf\Bundle\SimpleCmsBundle\Initializer\HomepageInitializer');
        $this->assertContainerBuilderHasService('cmf_simple_cms.persistence.phpcr.migrator.page', 'Symfony\Cmf\Bundle\SimpleCmsBundle\Migrator\Phpcr\Page');
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('cmf_simple_cms.persistence.phpcr.menu_provider', 'setManagerName', array(
            '%cmf_simple_cms.persistence.phpcr.manager_name%',
        ));
        $this->assertContainerBuilderHasService('cmf_simple_cms.persistence.phpcr.admin.page', 'Symfony\Cmf\Bundle\SimpleCmsBundle\Admin\PageAdmin');
        $this->assertContainerBuilderHasParameter('cmf_simple_cms.ivory_ckeditor.config', array('config_name' => 'cmf_simple_cms'));
    }

    public function testLoadMinimal()
    {
        $this->setParameter('kernel.bundles', array('CmfRoutingBundle' => true));

        $this->load(array(
            'persistence' => array(
                'phpcr' => array(
                    'enabled' => true,
                    'use_sonata_admin' => false,
                ),
            ),
            'use_menu' => false,
            'ivory_ckeditor' => false,
        ));

        $this->assertContainerBuilderNotHasService('cmf_simple_cms.persistence.phpcr.admin.page');
        $this->assertContainerBuilderNotHasService('cmf_simple_cms.persistence.phpcr.menu_provider');
        $this->assertContainerBuilderHasParameter('cmf_simple_cms.ivory_ckeditor.config', array());
    }

    public function testEnableIvoryCkeditor()
    {
        $this->setParameter('kernel.bundles', array('CmfRoutingBundle' => true, 'IvoryCKEditorBundle' => true));

        $this->load(array(
            'persistence' => array('phpcr' => array('enabled' => false)),
            'ivory_ckeditor' => array('config_name' => 'default'),
        ));

        $this->assertContainerBuilderHasParameter('cmf_simple_cms.ivory_ckeditor.config', array(
            'config_name' => 'default',
        ));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage IvoryCKEditorBundle integration was explicitely enabled, but the bundle is not available
     */
    public function testFailIfIvoryCkeditorEnabledButNotAvailable()
    {
        $this->setParameter('kernel.bundles', array('CmfRoutingBundle' => true));

        $this->load(array(
            'persistence' => array('phpcr' => array('enabled' => false)),
            'ivory_ckeditor' => array('enabled' => true),
        ));
    }
}
