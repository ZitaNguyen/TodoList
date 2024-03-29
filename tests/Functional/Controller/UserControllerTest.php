<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Tests\Helper\LoginUser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserControllerTest extends LoginUser
{
    public function testListUser(): void
    {
        $this->loginAdminUser();

        $crawler = $this->client->request('GET', '/users');

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');

        $this->assertEquals(1, $crawler->filter('table')->count());
        $this->assertEquals(1, $crawler->filter('.btn.btn-primary')->count());
        $this->assertEquals(1, $crawler->filter('.btn.btn-danger')->count());

    }

    public function testCreateUser(): void
    {
        $this->loginAdminUser();

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

        $this->client->followRedirect();

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

        $this->client->followRedirect();
        $this->assertRouteSame('user_list');

        $this->assertSelectorTextContains(
            'div.alert.alert-success',
            "Superbe ! L'utilisateur a bien été supprimé."
        );
    }

    public function testEditUser(): void
    {
        $this->loginAdminUser();

        $entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $users = $entityManager->getRepository(User::class)->findAll();
        $userId = $users[0]->getId();
        $crawler = $this->client->request('GET', '/users/'.$userId.'/edit');

        $this->assertResponseIsSuccessful();

        // Get and Fill in the form
        $form = $crawler->selectButton('Modifier')->form();
        $form['user[username]'] = $users[0]->getUsername();
        $form['user[email]'] = $users[0]->getEmail();
        $form['user[roles]'] = ['ROLE_ADMIN', 'ROLE_USER'];

        // Submit the form
        $this->client->submit($form);

        // Assert that the user is modified successfully
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $this->client->followRedirect();
        $this->assertRouteSame('user_list');

         $this->assertSelectorTextContains(
            'div.alert.alert-success',
            "Superbe ! L'utilisateur a bien été modifié."
        );
    }
}
