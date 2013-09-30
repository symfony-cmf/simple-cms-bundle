<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Tests\Functional\Migrator\Phpcr;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\Phpcr\Page;

class PageTest extends BaseTestCase
{
    public function setUp()
    {
        $this->db('PHPCR')->createTestNode();
        $this->dm = $this->db('PHPCR')->getOm();
        $this->baseNode = $this->dm->find(null, '/test');
    }

    public function testPage()
    {
        $data = array(
            'name' => 'page-name',
            'title' => 'Page Title',
            'label' => 'Page Label',
            'body' => 'This is body',
            'createDate' => new \DateTime('2013-07-05'),
            'publishable' => false,
            'publishStartDate' => new \DateTime('2013-06-18'),
            'publishEndDate' => new \DateTime('2013-06-18'),
            'addLocalePattern' => true,
            'extras' => array(
                'extra_1' => 'foobar',
                'extra_2' => 'barfoo',
            ),
        );

        $page = new Page;
        $refl = new \ReflectionClass($page);

        $page->setParent($this->baseNode);

        foreach ($data as $key => $value) {
            $refl = new \ReflectionClass($page);
            $prop = $refl->getProperty($key);
            $prop->setAccessible(true);
            $prop->setValue($page, $value);
        }

        $this->dm->persist($page);
        $this->dm->flush();
        $this->dm->clear();

        $page = $this->dm->find(null, '/test/page-name');

        $this->assertNotNull($page);

        foreach ($data as $key => $value) {
            $prop = $refl->getProperty($key);
            $prop->setAccessible(true);
            $v = $prop->getValue($page);

            if (!is_object($value)) {
                $this->assertEquals($value, $v);
            }
        }

        // test publish start and end
        $publishStartDate = $page->getPublishStartDate();
        $publishEndDate = $page->getPublishEndDate();

        $this->assertInstanceOf('\DateTime', $publishStartDate);
        $this->assertInstanceOf('\DateTime', $publishEndDate);
        $this->assertEquals($data['publishStartDate']->format('Y-m-d'), $publishStartDate->format('Y-m-d'));
        $this->assertEquals($data['publishEndDate']->format('Y-m-d'), $publishEndDate->format('Y-m-d'));

        // test multi-lang
        $page->setLocale('fr');
        $this->dm->persist($page);
        $this->dm->flush();
        $this->dm->clear();

        $page = $this->dm->findTranslation(null, '/test/page-name', 'fr');
        $this->assertEquals('fr', $page->getLocale());

        // test node
        $node = $page->node;
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
    }
}
