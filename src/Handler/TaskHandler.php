<?php

namespace App\Handler;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator as OrmPaginator;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class TaskHandler
{
    public function __construct(
        private readonly TaskRepository         $taskRepository,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function getAll(Request $request, PaginatorInterface $paginator): PaginationInterface
    {
        $page = $request->query->get('page');
        $limit = $request->query->get('limit');

        return $this->taskRepository->findAllWithPagination($paginator, $limit, $page);
    }

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id): void
    {
        $todo = $this->taskRepository->find($id);
        if ($todo) {
            $this->entityManager->remove($todo);
             $this->entityManager->flush();
        }
    }

    public function create($request): void
    {
        $todo = $request->getPayload();

        $newTodo = new Task();
        $newTodo->setName($todo->get('name'));
        $newTodo->setDescription($todo->get('description'));
        $newTodo->setIsCompleted(false);
        $this->entityManager->persist($todo);
        $this->entityManager->flush();
    }

    public function update($id, $todo): void
    {
        $tmpTodo = $this->taskRepository->find($id);
            if($tmpTodo){
                $tmpTodo->setName($todo->get('name'));
                $tmpTodo->setDescription($todo->get('description'));

            }
        $this->entityManager->flush();
    }

    /**
     * @param int $id
     * @return void
     */
    public function completed(int $id): void
    {
        $tmpTodo = $this->taskRepository->find($id);
        $tmpTodo?->setIsCompleted(true);
        $this->entityManager->flush();
    }
}