<?php

namespace App\Repository;

use App\Entity\Tool;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tool>
 */
class ToolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tool::class);
    }

    /**
     * Trouve un tool par son slug
     */
    public function findOneBySlug(string $slug): ?Tool
    {
        return $this->findOneBy(['slug' => $slug, 'active' => true]);
    }

    /**
     * Récupère tous les tools actifs
     */
    public function findAllActive(): array
    {
        return $this->findBy(['active' => true], ['name' => 'ASC']);
    }
}
