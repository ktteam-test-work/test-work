<?php

namespace App\Tests\Form\Type;

use App\Form\UserType;
use Symfony\Component\Form\Test\TypeTestCase;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormExtensionInterface;

/**
 * Class UserTypeTest
 * @package App\Tests\Form\Type
 */
class UserTypeTest extends TypeTestCase
{
    protected function getExtensions()
    {
        $mockEntityManager = $this->createMock(EntityManager::class);
        $mockEntityManager->method('getClassMetadata')
            ->willReturn(new ClassMetadata(UserType::class));

        $entityRepository = $this->createMock(EntityRepository::class);
        $entityRepository->method('createQueryBuilder')
            ->willReturn(new QueryBuilder($mockEntityManager));
        $mockEntityManager->method('getRepository')->willReturn($entityRepository);

        $mockRegistry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['getManagerForClass'])
            ->getMock();

        $mockRegistry->method('getManagerForClass')
            ->willReturn($mockEntityManager);

        /** @var EntityType|\PHPUnit_Framework_MockObject_MockObject $mockEntityType */
        $mockEntityType = $this->getMockBuilder(EntityType::class)
            ->setConstructorArgs([$mockRegistry])
            ->setMethodsExcept(['configureOptions', 'getParent'])
            ->getMock();

        $mockEntityType->method('getLoader')->willReturnCallback(function ($a, $b, $class) {
            return new class($class) implements EntityLoaderInterface
            {
                /**
                 * @var
                 */
                private $class;

                /**
                 *  constructor.
                 *
                 * @param $class
                 */
                public function __construct($class)
                {
                    $this->class = $class;
                }

                /**
                 * Returns an array of entities that are valid choices in the corresponding choice list.
                 *
                 * @return array The entities
                 */
                public function getEntities()
                {
                    switch ($this->class) {
                        case UserType::class:
                            return [new UserType()];
                            break;
                    }
                    return [];
                }

                /**
                 * Returns an array of entities matching the given identifiers.
                 *
                 * @param string $identifier The identifier field of the object. This method
                 *                           is not applicable for fields with multiple
                 *                           identifiers.
                 * @param array $values The values of the identifiers
                 *
                 * @return array The entities
                 */
                public function getEntitiesByIds($identifier, array $values)
                {
                    // TODO: Implement getEntitiesByIds() method.
                }

            };
        });

        return [
            new class($mockEntityType) implements FormExtensionInterface
            {
                private $type;

                public function __construct($type)
                {
                    $this->type = $type;
                }

                public function getType($name)
                {
                    return $this->type;
                }

                public function hasType($name)
                {
                    return $name === EntityType::class;
                }

                public function getTypeExtensions($name)
                {
                    return [];
                }

                public function hasTypeExtensions($name)
                {
                }

                public function getTypeGuesser()
                {
                }
            },
        ];
    }

    public function testSubmitValidData()
    {
        $formData = [
            "alias" => "new-user-alias",
            "firstname" => "new-user-firstname",
            "lastname" => "new-user-lastname",
        ];
        $form = $this->factory->create(UserType::class);
        $object = new User();
        $object->setAlias('new-user-alias');
        $object->setFirstName('new-user-firstname');
        $object->setLastName('new-user-lastname');
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($object, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}