<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * Save Task entity.
     */
    public function save(Task $task, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->persist($task);
        if ($flush) {
            $em->flush();
        }
    }

    /**
     * Remove Task entity.
     */
    public function remove(Task $task, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->remove($task);
        if ($flush) {
            $em->flush();
        }
    }

    /**
     * Find tasks with optional status filter and pagination.
     *
     * @param string|null $status Filter by status (e.g. 'todo', 'in_progress', 'done')
     * @param int $page 1-based page number
     * @param int $limit items per page
     *
     * @return array{items: Task[], total: int, page: int, limit: int}
     */
    public function findByStatusWithPagination(?string $status, int $page = 1, int $limit = 10): array
    {
        $page = max(1, $page);
        $limit = max(1, min(100, $limit)); // ограничение: не больше 100

        $qb = $this->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC');

        if ($status !== null && $status !== '') {
            $qb->andWhere('t.status = :status')
               ->setParameter('status', $status);
        }

        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        $query = $qb->getQuery();

        // Doctrine Paginator даст корректный total
        $paginator = new Paginator($query, true);
        $total = count($paginator);

        $items = [];
        foreach ($paginator as $task) {
            $items[] = $task;
        }

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }
}
