<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserTest extends WebTestCase
{
    private $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function loginUser(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form();
        $this->client->submit($form, [
            '_username' => 'Zita',
            '_password' => 'test'
        ]);
    }

    public function testListUser(): void
    {
        $this->loginUser();

        $crawler = $this->client->request('GET', '/users');

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');

        $this->assertEquals(1, $crawler->filter('table')->count());
        $this->assertEquals(1, $crawler->filter('.btn.btn-primary')->count());
        $this->assertEquals(1, $crawler->filter('.btn.btn-danger')->count());

    }

    public function testCreateUser(): void
    {
        $this->loginUser();

        $crawler = $this->client->request('GET', '/users/create');

        $this->assertResponseIsSuccessful();

        // Get and Fill in the form
        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = 'New User';
        $form['user[password][first]'] = 'test';
        $form['user[password][second]'] = 'test';
        $form['user[email]'] = 'test@test.fr';

        // Submit the form
        $this->client->submit($form);

        // Assert that the user is created successfully
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $crawler = $this->client->followRedirect();

         $this->assertSelectorTextContains(
            'div.alert.alert-success',
            "Superbe ! L'utilisateur a bien été ajouté."
        );

        // Delete the user of this test
        $entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => 'New User']);
        $userId = $user->getId();
        $this->client->request('DELETE', '/users/'.$userId.'/delete');

        // Assert that the user is deleted successfully
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $crawler = $this->client->followRedirect();
        $this->assertRouteSame('user_list');

        $this->assertSelectorTextContains(
            'div.alert.alert-success',
            "Superbe ! L'utilisateur a bien été supprimé."
        );
    }

    // public function testEditUser(): void
    // {
    //     $this->loginUser();

    //     $entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    //     $user = $entityManager->find(User::class, 1);
    //     $userId = $user->getId();
    //     $crawler = $this->client->request('GET', '/users/'.$userId.'/edit');

    //     $this->assertResponseIsSuccessful();

    //     // Get and Fill in the form
    //     $form = $crawler->selectButton('Modifier')->form();
    //     $form['user[username]'] = $user->getUsername();
    //     $form['user[email]'] = $user->getEmail();
    //     $form['user[roles]'] = ['ROLE_ADMIN'];

    //     // Submit the form
    //     $this->client->submit($form);

    //     // Assert that the user is modified successfully
    //     $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

    //     $crawler = $this->client->followRedirect();
    //     $this->assertRouteSame('user_list');

    //      $this->assertSelectorTextContains(
    //         'div.alert.alert-success',
    //         "Superbe ! L'utilisateur a bien été modifié."
    //     );
    // }
}
