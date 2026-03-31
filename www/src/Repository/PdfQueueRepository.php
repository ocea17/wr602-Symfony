<?php

namespace App\Repository;

use App\Entity\PdfQueue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PdfQueue>
 */
class PdfQueueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PdfQueue::class);
    }

    public function findPendingQueue(int $limit = 10): array
    {
        return $this->createQueryBuilder('q')
            ->where('q.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('q.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
