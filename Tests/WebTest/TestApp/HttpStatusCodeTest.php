<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Tests\WebTest\TestApp;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

class HttpStatusCodeTest extends BaseTestCase
{
    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        $this->db('PHPCR')->loadFixtures(array(
            'Symfony\Cmf\Bundle\SimpleCmsBundle\Tests\Resources\DataFixtures\Phpcr\LoadPageData',
        ));
        $this->client = $this->createClient();
    }

    public function provideStatusCodeTest()
    {
        return array(
            array('/', 301),
            array('/en/homepage'),
            array('/en/french-page'),
            array('/no-locale-prefix'),
        );
    }

    /**
     * @dataProvider provideStatusCodeTest
     */
    public function testStatusCode($url, $expectedStatusCode = 200)
    {
        $this->client->request('GET', $url);
        $res = $this->client->getResponse();
        $this->assertEquals($expectedStatusCode, $res->getStatusCode());
    }
}
