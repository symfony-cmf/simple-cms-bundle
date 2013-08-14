<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Tests\Unit\Doctrine\Phpcr;

use Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\Phpcr\Page;

class PageTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSet()
    {
        $page = new Page;

        $page->setAddLocalePattern(true);
        $this->assertEquals(true, $page->getAddLocalePattern());
    }
}
