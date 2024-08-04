<?php

namespace App\Repository;

use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Author>
 */
class AuthorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }
    
    public function findByIds(array $ids): array
    {
       return $this->createQueryBuilder('a')
           ->where('a.id IN(:ids)')
           ->setParameter('ids', $ids)
           ->orderBy('a.id', 'ASC')
           ->getQuery()
           ->getResult()
       ;
    }
}
