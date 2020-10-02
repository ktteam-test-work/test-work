<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $alias;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $lastname;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param mixed $alias
     */
    public function setAlias($alias): void
    {
        $this->alias = $alias;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstname;
    }

    /**
     * @param mixed $firstname
     */
    public function setFirstName($firstname): void
    {
        $this->firstname = $firstname;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastname;
    }

    /**
     * @param mixed $lastname
     */
    public function setLastName($lastname): void
    {
        $this->lastname = $lastname;
    }

    public function __toString()
    {
        return (string)$this->id;
    }

    /**
     * @param User $obj
     * @return []
     */
    public static function getArrayForJsonOutput($obj)
    {
        $userArray = array();
        $userArray['user']['id'] = $obj->getId();
        $userArray['user']['alias'] = $obj->getAlias();
        $userArray['user']['firstname'] = $obj->getFirstName();
        $userArray['user']['lastname'] = $obj->getLastName();

        return $userArray;
    }
}
