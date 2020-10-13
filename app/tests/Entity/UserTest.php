<?php declare(strict_types = 1);

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Class UserTest
 * @package App\Tests\Entity
 */
class UserTest extends TestCase
{
    public function testItCanBeCreatedFromRawData()
    {
        $user = new User();
        $user->setAlias('test_alias');
        $user->setFirstName('test_firstname');
        $user->setLastName('test_lastname');

        $this->assertSame('test_alias', $user->getAlias());
        $this->assertSame('test_firstname', $user->getFirstName());
        $this->assertSame('test_lastname', $user->getLastName());
    }
}