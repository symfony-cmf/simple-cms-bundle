<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Tests\Unit\Doctrine\Phpcr;

use Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\Phpcr\Page;

class PageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group legacy
     */
    public function testAddLocalePattern()
    {
        $page = new Page();
        $page->setAddLocalePattern(true);

        $this->assertEquals(true, $page->getAddLocalePattern());
    }

    /**
     * @group legacy
     */
    public function testCreateDate()
    {
        $date = $this->prophesize('\DateTime')->reveal();

        $page = new Page();
        $page->setPublishStartDate($date);

        $this->assertEquals($date, $page->getCreateDate());
    }
}
