<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class UsersController
 * @package App\Controller
 */
class UsersController extends AbstractController
{

    /** @var UserRepository $userRepository */
    private $userRepository;
    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * UsersController constructor.
     * @param UserRepository $userRepository
     * @param PaginatorInterface $paginator
     */
    public function __construct(
        UserRepository $userRepository,
        PaginatorInterface $paginator
    )
    {
        $this->userRepository = $userRepository;
        $this->paginator = $paginator;
    }

    /**
     * @Route("/users_index", name="users_index")
     */
    public function index(Request $request)
    {
        $users = $this->userRepository->findAll();
        $pagination = $this->paginator->paginate($users, $request->query->getInt('page', 1), 5);

        return $this->render('users/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/users", name="users")
     */
    public function users(Request $request)
    {
        $users = $this->userRepository->findAll();
        $usersArr = array();

        foreach ($users as $user) {
            $userItem = User::getArrayForJsonOutput($user);
            $usersArr[] = $userItem;
        }

        return new JsonResponse($usersArr);
    }

    /**
     * @Route("/users/new", name="new_user_post")
     */
    public function addUser(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userData = $request->request->get('user');
            $user->setAlias($userData['alias']);
            $user->setFirstName($userData['firstname']);
            $user->setLastName($userData['lastname']);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $headers = [
                'Location' => $this->generateUrl('user_show', ['id' => $user->getId()]),
            ];

            return new JsonResponse(User::getArrayForJsonOutput($user), JsonResponse::HTTP_CREATED, $headers);

        }
        return $this->render('users/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/users/{id}", name="user_show")
     */
    public function user(User $user)
    {
        $user = User::getArrayForJsonOutput($user);

        return new JsonResponse($user);
    }

    /**
     * @Route("/users/{id}/edit", name="user_edit")
     */
    public function edit(User $user, Request $request)
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userData = $request->request->get('user');
            $user->setAlias($userData['alias']);
            $user->setFirstName($userData['firstname']);
            $user->setLastName($userData['lastname']);
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $headers = [
                'Location' => $this->generateUrl('user_show', ['id' => $user->getId()]),
            ];

            return new JsonResponse(User::getArrayForJsonOutput($user), JsonResponse::HTTP_CREATED, $headers);

        }

        return $this->render('users/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/users/{id}/delete", name="user_delete")
     */
    public function delete(User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();
        $users = $this->userRepository->findAll();
        $usersArr = array();

        foreach ($users as $user) {
            $userItem = User::getArrayForJsonOutput($user);
            $usersArr[] = $userItem;
        }

        $headers = [
            'Location' => $this->generateUrl('users'),
        ];

        return new JsonResponse($usersArr, JsonResponse::HTTP_OK, $headers);
    }
}
