<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Tests\WebTest\TestApp;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

class HttpStatusCodeTest extends BaseTestCase
{
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
            array('/'),
            array('/en/homepage'),
            array('/en/french-page'),
            array('/no-locale-prefix'),
        );
    }

    public function testPageList()
    {
        $crawler = $this->client->request('GET', '/admin/cmf/simplecms/page/list');
        $res = $this->client->getResponse();
        $this->assertEquals(200, $res->getStatusCode());
    }
}
