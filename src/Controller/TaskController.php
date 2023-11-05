<?php

namespace App\Controller;

use App\dto\TaskDto;
use App\Entity\Task;
use App\Handler\TaskHandler;
use Dungtyner\StructureDictator\StructureDictatorBundle;
use Knp\Component\Pager\PaginatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'task')]
class TaskController extends AbstractController
{
    public function __construct(
        private readonly TaskHandler $taskHandler,
    )
    {
    }

    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    #[Route('/todo', name: 'app_todo')]
    #[OA\Parameter(name: 'limit')]
    #[OA\Parameter(name: 'page')]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $pagination = $this->taskHandler->getAll($request, $paginator);
        $bundle = new  StructureDictatorBundle();
        return $this->render('todo/index.html.twig', [
            'pagination' => $pagination,
            'bundle' => $bundle->getContainerExtension()
        ]);
    }

    /**
     * @param int $id
     * @return RedirectResponse
     */
    #[Route('/delete/{id}', name: 'delete')]
    public function delete(int $id): RedirectResponse
    {
        $this->taskHandler->delete($id);
        return $this->redirectToRoute('app_todo');
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/api/todo/{id}/delete', name: 'triggerDeleteTask', methods: ['GET'])]
    public function triggerDeleteTask(int $id): JsonResponse
    {
        $this->taskHandler->delete($id);
        return $this->json(['success'=>true]);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/todo/{id}/update', name: 'triggerUpdateTask', methods: ['POST'])]
    #[OA\RequestBody(
        description: 'Update Task',
        content: new OA\JsonContent(
            ref: new Model(type: TaskDto::class)
        )
    )]
    public function triggerUpdateTask(int $id, Request $request): JsonResponse
    {
        $todo = $request->getPayload();
        $this->taskHandler->update($id, $todo);
        return $this->json(['success'=>true]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/api/todo/{id}/completed', name: 'triggerCompletedTask', methods: ['POST'])]
    public function triggerCompleteTask(int $id): JsonResponse
    {
        $this->taskHandler->completed($id);
        return $this->json(['success'=>true]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    #[Route('/todo/create', name: 'create')]
    public function create(Request $request): RedirectResponse|Response
    {
        $todo = new Task();
        $todo->setName('');
        $todo->setDescription('');

        $form = $this->createFormBuilder($todo)
            ->add('name',TextType::class)
            ->add('description',TextType::class)
            ->add('save', SubmitType::class, ['label' =>'Add todo'])
            ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $this->taskHandler->create($request);
            return $this->redirectToRoute('app_todo');
        }
        return $this->render('todo/add.html.twig',[
            'form'=> $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/todo/create', name: 'triggerCreateHandle', methods: ['POST'])]
    #[OA\RequestBody(
        description: 'Create Task',
        content: new OA\JsonContent(
            ref: new Model(type: TaskDto::class, groups: ['create']),
        )
    )]
    public function triggerCreateTask(Request $request): JsonResponse
    {
        $this->taskHandler->create($request);
        return $this->json(['success'=>true]);
    }

    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return JsonResponse
     */
    #[Route('/api/todo/all', name: 'getList', methods: ['GET'])]
    #[OA\Parameter(name: 'limit', in: 'query')]
    #[OA\Parameter(name: 'page', in: 'query')]
    public function getAll(Request $request, PaginatorInterface $paginator): JsonResponse
    {
        return $this->json($this->taskHandler->getAll($request, $paginator));
    }
}
