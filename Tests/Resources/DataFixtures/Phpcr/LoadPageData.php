<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Tests\Resources\DataFixtures\Phpcr;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ODM\PHPCR\Document\Generic;
use Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\Phpcr\Page;

class LoadPageData implements FixtureInterface, DependentFixtureInterface
{
    public function getDependencies()
    {
        return array(
            'Symfony\Cmf\Component\Testing\DataFixtures\PHPCR\LoadBaseData',
        );
    }

    public function load(ObjectManager $manager)
    {
        $root = $manager->find(null, '/test');

        $base = new Page;
        $base->setName('page');
        $base->setLabel('Simple Cmf');
        $base->setParent($root);
        $manager->persist($base);

        $page = new Page;
        $page->setName('homepage');
        $page->setTitle('Homepage');
        $page->setLabel('homepage');
        $page->setPublishable(true);
        $page->setParent($base);
        $manager->persist($page);

        $page = new Page;
        $page->setName('simple-cms');
        $page->setTitle('Simple CMS');
        $page->setLabel('Simple CMS');
        $page->setPublishable(true);
        $page->setParent($base);
        $manager->persist($page);

        $page = new Page;
        $page->setName('contact');
        $page->setTitle('Contact');
        $page->setLabel('Contact');
        $page->setPublishable(true);
        $page->setParent($base);
        $manager->persist($page);

        $manager->flush();
    }
}
