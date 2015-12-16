<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Initializer;

use PHPCR\Util\NodeHelper;
use PHPCR\Util\PathHelper;
use Doctrine\Bundle\PHPCRBundle\Initializer\InitializerInterface;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;

class HomepageInitializer implements InitializerInterface
{
    private $basePath;
    private $documentClass;

    public function __construct($basePath, $documentClass)
    {
        $this->basePath = $basePath;
        $this->documentClass = $documentClass;
    }

    /**
     * {@inheritdoc}
     */
    public function init(ManagerRegistry $registry)
    {
        /** @var $dm DocumentManager */
        $dm = $registry->getManagerForClass('Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\Phpcr\Page');
        if ($dm->find(null, $this->basePath)) {
            return;
        }

        $session = $dm->getPhpcrSession();
        NodeHelper::createPath($session, PathHelper::getParentPath($this->basePath));

        $page = new $this->documentClass();
        $page->setId($this->basePath);
        $page->setLabel('Home');
        $page->setTitle('Homepage');
        $page->setBody('Autocreated Homepage');

        $dm->persist($page);
        $dm->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'CmfSimpleCmsBundle Homepage';
    }
}
