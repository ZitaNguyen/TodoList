<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testHomePage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1', "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !");

        $this->assertEquals(2, $crawler->filter('.btn.btn-success')->count());
        $this->assertEquals(1, $crawler->filter('.btn.btn-info')->count());
        $this->assertEquals(1, $crawler->filter('.btn.btn-primary')->count());

    }
}
