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

    protected function getContent($filename)
    {
        return file_get_contents(__DIR__.'/content/'.$filename);
    }

    public function load(ObjectManager $manager)
    {
        $root = $manager->find(null, '/test');

        $base = new Page;
        $base->setName('page');
        $base->setTitle('Simple Cms');
        $base->setLabel('Simple Cms');
        $base->setParent($root);
        $manager->persist($base);

        $page = new Page;
        $page->setName('homepage');
        $page->setTitle('Homepage');
        $page->setLabel('Homepage');
        $page->setPublishable(true);
        $page->setParent($base);
        $page->setBody($this->getContent('homepage.html'));
        $manager->persist($page);

        $page = new Page;
        $page->setName('french-page');
        $page->setTitle('French Page');
        $page->setLabel('French Page');
        $page->setPublishable(true);
        $page->setLocale('fr');
        $page->setBody($this->getContent('french-page.html'));
        $page->setParent($base);
        $manager->persist($page);

        $page = new Page;
        $page->setName('no-locale-prefix');
        $page->setTitle('No Locale Prefix');
        $page->setLabel('No Locale Prefix');
        $page->setPublishable(true);
        $page->setAddLocalePattern(false);
        $page->setParent($base);
        $page->setBody($this->getContent('no-locale-prefix.html'));
        $page->setParent($base);
        $manager->persist($page);

        $manager->flush();
    }
}
