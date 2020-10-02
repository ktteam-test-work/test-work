<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Service\ElasticsearchService;
use App\Form\SearchFormType;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\UserRepository;

/**
 * Class TasksController
 * @package App\Controller
 */
class TasksController extends AbstractController
{

    /** @var TaskRepository $taskRepository */
    private $taskRepository;
    /**
     * @var PaginatorInterface
     */
    private $paginator;
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * TasksController constructor.
     * @param TaskRepository $taskRepository
     * @param PaginatorInterface $paginator
     * @param UserRepository $userRepository
     */
    public function __construct(
        TaskRepository $taskRepository,
        PaginatorInterface $paginator,
        UserRepository $userRepository
    )
    {
        $this->taskRepository = $taskRepository;
        $this->paginator = $paginator;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(ElasticsearchService $elasticSearchService, Request $request)
    {
        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $formData = $searchForm->getData();
            $tasks = $elasticSearchService->getList($formData);
        } else {
            $tasks = $this->taskRepository->findAll();
        }

        $pagination = $this->paginator->paginate($tasks, $request->query->getInt('page', 1), 5);

        return $this->render('tasks/index.html.twig', [
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination
        ]);
    }


    /**
     * @Route("/tasks", name="tasks")
     */
    public function tasks(ElasticsearchService $elasticSearchService, Request $request)
    {
        $tasks = $this->taskRepository->findAll();
        $tasksArr = array();

        foreach ($tasks as $task) {
            $taskItem = Task::getArrayForJsonOutput($task);
            $tasksArr[] = $taskItem;
        }

        return new JsonResponse($tasksArr);

    }

    /**
     * @Route("/tasks/new", name="new_task_post")
     */
    public function addTask(Request $request)
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user_id = $data->getUser();
            $user = $this->userRepository->find($user_id);
            $task->setUser($user);
            $task->setCreatedAt(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();
            $headers = [
                'Location' => $this->generateUrl('task_show', ['id' => $task->getId()]),
            ];

            return new JsonResponse(Task::getArrayForJsonOutput($task), JsonResponse::HTTP_CREATED, $headers);

        }
        return $this->render('tasks/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/tasks/{id}", name="task_show")
     */
    public function task(Task $task)
    {
        $task = Task::getArrayForJsonOutput($task);

        return new JsonResponse($task);

    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     */
    public function edit(Task $task, Request $request)
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $headers = [
                'Location' => $this->generateUrl('task_show', ['id' => $task->getId()]),
            ];

            return new JsonResponse(Task::getArrayForJsonOutput($task), JsonResponse::HTTP_CREATED, $headers);

        }

        return $this->render('tasks/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     */
    public function delete(Task $task)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($task);
        $em->flush();
        $tasks = $this->taskRepository->findAll();
        $tasksArr = array();

        foreach ($tasks as $task) {
            $taskItem = Task::getArrayForJsonOutput($task);
            $tasksArr[] = $taskItem;
        }

        $headers = [
            'Location' => $this->generateUrl('tasks'),
        ];

        return new JsonResponse($tasksArr, JsonResponse::HTTP_OK, $headers);
    }
}
