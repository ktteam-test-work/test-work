<?php declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Class TaskTest
 * @package App\Tests\Entity
 */
class TaskTest extends TestCase
{
    public function testItCanBeCreatedFromRawData()
    {
        $task = new Task();
        $task->setTitle('test_title');
        $task->setBody('test_body');
        $user = new User();
        $user->setAlias('test_alias');
        $task->setUser($user);

        $this->assertSame('test_title', $task->getTitle());
        $this->assertSame('test_body', $task->getBody());
        $this->assertSame('test_alias', $task->getUser()->getAlias());
    }
}