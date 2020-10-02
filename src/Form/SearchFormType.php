<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Repository\UserRepository;

/**
 * Class SearchFormType
 * @package App\Form
 */
class SearchFormType extends AbstractType
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * SearchFormType constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userRepository = $this->userRepository;
        $users = $userRepository->findAll();
        $usersAliases = array();
        foreach ($users as $user) {
            $usersAliases[$user->getAlias()] = $user->getId();
        }
        $builder
            ->add('user', ChoiceType::class, array(
                'choices' => $usersAliases
            ))
            ->add('date_start', TextType::class)
            ->add('date_end', TextType::class)
            ->add('submit', SubmitType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
