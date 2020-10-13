<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Task;
use function json_decode;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class TaskControllerTest
 * @package App\Tests\Controller
 */
class TaskControllerTest extends WebTestCase
{
    public function testListingAllTasks()
    {
        $client = static::createClient();
        $client->request('GET', '/tasks');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertJson($response->getContent());
    }

    public function testGettingSingleTask()
    {
        $client = static::createClient();
        $client->request('GET', '/tasks/12');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $task = json_decode($response->getContent());
        $this->assertSame(12, $task->task->id);
    }

    public function testCreatingTask()
    {
        $client = static::createClient();
        $token = $client->getContainer()->get('security.csrf.token_manager')->getToken('task')->getValue();
        $task = ['task' => ['title' => 'test-title', 'body' => 'test-body', 'user' => '3', '_token' => $token]];
        $client->request('POST', '/tasks/new', $task, [], []);
        $response = $client->getResponse();

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertTrue($response->headers->has('Location'));
        $this->assertContains('/tasks/21', $response->headers->get('Location'));

        $client->request('GET', $response->headers->get('Location'));
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $responseJsonDecoded = json_decode($response->getContent());
        $this->assertEquals($task['task']['title'], $responseJsonDecoded->task->title);
        $this->assertEquals($task['task']['body'], $responseJsonDecoded->task->body);
    }

    public function testUpdatingExistingTask()
    {
        $client = static::createClient();
        $task = new Task();
        $task->setBody('new-test-body');
        $token = $client->getContainer()->get('security.csrf.token_manager')->getToken('task')->getValue();
        $taskJson = ['task' => ['title' => 'test-title', 'body' => $task->getBody(), 'user' => '3', '_token' => $token]];
        $client->request('POST', '/tasks/21/edit', $taskJson, [], []);
        $response = $client->getResponse();

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $updatedTask = json_decode($response->getContent());

        $this->assertEquals(21, $updatedTask->task->id);
        $this->assertSame('test-title', $updatedTask->task->title);
        $this->assertSame($task->getBody(), $updatedTask->task->body);
        $this->assertEquals(3, $updatedTask->task->user);
    }

    public function testDeletingTask()
    {
        $client = static::createClient();
        $client->request('DELETE', '/tasks/21/delete');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $client->request('GET', '/tasks/21');
        $response = $client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
    }
}