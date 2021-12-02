<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConferenceControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        echo $client->getResponse();

        $this->assertResponseIsSuccessful();
//        $this->assertSelectorTextContains('h2', 'Give you feedback');
    }

    public function testConferencePage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertCount(2, $crawler->filter('h4'));

        $client->clickLink('View');

        $this->assertPageTitleContains('');

    }
}
