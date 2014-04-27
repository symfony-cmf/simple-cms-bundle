<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Tests\Functional\Doctrine\Phpcr;

use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\Phpcr\Page;

class PageTest extends BaseTestCase
{
    /**
     * @var DocumentManager
     */
    private $dm;
    private $baseDocument;

    public function setUp()
    {
        $this->db('PHPCR')->createTestNode();
        $this->dm = $this->db('PHPCR')->getOm();
        $this->baseDocument = $this->dm->find(null, '/test');
    }

    public function testPage()
    {
        $page = new Page(array('add_locale_pattern' => true));
        $page->setParentDocument($this->baseDocument);
        $page->setName('page-name');
        $page->setTitle('Page Title');
        $page->setLabel('Page Label');
        $page->setBody('This is body');
        $page->setPublishable(false);
        $page->setPublishStartDate(new \DateTime('2013-06-18'));
        $page->setPublishEndDate(new \DateTime('2013-06-18'));
        $page->setExtras(array(
            'extra_1' => 'foobar',
            'extra_2' => 'barfoo',
        ));

        $this->dm->persist($page);
        $this->dm->flush();
        $this->dm->clear();

        $page = $this->dm->find(null, '/test/page-name');

        $this->assertNotNull($page);
        $this->assertTrue($page->getOption('add_locale_pattern'));
        $this->assertEquals('Page Title', $page->getTitle());
        $this->assertEquals('Page Label', $page->getLabel());
        $this->assertEquals('This is body', $page->getBody());
        $this->assertEquals(array(
            'extra_1' => 'foobar',
            'extra_2' => 'barfoo',
        ), $page->getExtras());

        // test publish start and end
        $publishStartDate = $page->getPublishStartDate();
        $publishEndDate = $page->getPublishEndDate();

        $this->assertInstanceOf('\DateTime', $publishStartDate);
        $this->assertInstanceOf('\DateTime', $publishEndDate);
        $this->assertEquals('2013-06-18', $publishStartDate->format('Y-m-d'));
        $this->assertEquals('2013-06-18', $publishEndDate->format('Y-m-d'));

        // test multi-lang
        $page->setLocale('fr');
        $page->setTitle('french');
        $this->dm->persist($page);
        $this->dm->flush();
        $this->dm->clear();

        $page = $this->dm->findTranslation(null, '/test/page-name', 'fr');
        $this->assertEquals('fr', $page->getLocale());
        $this->assertEquals('french', $page->getTitle());

        // test node
        $node = $page->getNode();
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
    }
}
