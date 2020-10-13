<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use function json_decode;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class UserControllerTest
 * @package App\Tests\Controller
 */
class UserControllerTest extends WebTestCase
{
    public function testListingAllUsers()
    {
        $client = static::createClient();
        $client->request('GET', '/users');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGettingSingleUser()
    {
        $client = static::createClient();
        $client->request('GET', '/users/12');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $user = json_decode($response->getContent());
        $this->assertSame(12, $user->user->id);
    }

    public function testCreatingUser()
    {
        $client = static::createClient();
        $token = $client->getContainer()->get('security.csrf.token_manager')->getToken('user')->getValue();
        $user = ['user' => ['alias' => 'test-alias', 'firstname' => 'test-firstname', 'lastname' => 'test-lastname', '_token' => $token]];
        $client->request('POST', '/users/new', $user, [], []);
        $response = $client->getResponse();

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertTrue($response->headers->has('Location'));
        $this->assertContains('/users/21', $response->headers->get('Location'));

        $client->request('GET', $response->headers->get('Location'));
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $responseJsonDecoded = json_decode($response->getContent());
        $this->assertEquals($user['user']['alias'], $responseJsonDecoded->user->alias);
        $this->assertEquals($user['user']['firstname'], $responseJsonDecoded->user->firstname);
        $this->assertEquals($user['user']['lastname'], $responseJsonDecoded->user->lastname);
    }

    public function testUpdatingExistingUser()
    {
        $client = static::createClient();
        $user = new User();
        $user->setAlias('new-test-alias');
        $token = $client->getContainer()->get('security.csrf.token_manager')->getToken('user')->getValue();
        $userJson = ['user' => ['alias' => $user->getAlias(), 'firstname' => 'test-firstname', 'lastname' => 'test-lastname', '_token' => $token]];

        $client->request('POST', '/users/21/edit', $userJson, [], []);
        $response = $client->getResponse();

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $updatedUser = json_decode($response->getContent());

        $this->assertEquals(21, $updatedUser->user->id);
        $this->assertSame($user->getAlias(), $updatedUser->user->alias);
        $this->assertEquals('test-firstname', $updatedUser->user->firstname);
        $this->assertEquals('test-lastname', $updatedUser->user->lastname);
    }

    public function testDeletingUser()
    {
        $client = static::createClient();
        $client->request('DELETE', '/users/21/delete');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $client->request('GET', '/users/21');
        $response = $client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
    }
}