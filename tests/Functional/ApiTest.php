<?php

namespace Tests\Functional;

class ApiTest extends BaseTestCase
{
    /**
     * Test that the index route returns a rendered response containing the text 'SlimFramework' but not a greeting
     */
    public function testGetHomepageWithoutName()
    {
        $response = $this->runApp('GET', '/');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('SlimFramework', (string)$response->getBody());
        $this->assertNotContains('Hello', (string)$response->getBody());
    }

    /**
     * Test profile/facebook/4 (mark zuckerberg)
     */
    public function testGetMarkZuckerberg()
    {
        $response = $this->runApp('GET', '/profile/facebook/4');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('{"first_name":"Mark","last_name":"Zuckerberg","id":"4"}', (string)$response->getBody());
    }
    
    /**
     * Test config/check (api config)
     */
    public function testGetConfigCheck()
    {
        $response = $this->runApp('GET', '/config/check');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('{"result":"ok"}', (string)$response->getBody());
    }

}