<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use App\Entity\Task;
use App\Entity\User;

/**
 * Class AppFixtures
 * @package App\DataFixtures
 */
class AppFixtures extends Fixture
{
    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * AppFixtures constructor.
     */
    public function __construct()
    {
        $this->faker = Factory::create();
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadTasks($manager);
    }

    /**
     * @param ObjectManager $manager
     */
    public function loadTasks(ObjectManager $manager)
    {
        for ($i = 1; $i < 21; $i++) {
            $task = new Task();
            $task->setTitle($this->faker->text(20));
            $users = $manager->getRepository(User::class)->findAll();
            $task->setUser($this->faker->randomElement($users));
            $task->setBody($this->faker->text(1000));
            $task->setCreatedAt($this->faker->dateTime);
            $manager->persist($task);
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    public function loadUsers(ObjectManager $manager)
    {
        for ($i = 1; $i < 21; $i++) {
            $user = new User();
            $user->setAlias($this->faker->text(10));
            $user->setFirstName($this->faker->text(10));
            $user->setLastName($this->faker->text(10));
            $manager->persist($user);
        }

        $manager->flush();
    }
}
