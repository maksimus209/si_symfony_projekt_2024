<?php
/**
 * Question repository.
 */

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class QuestionRepository.
 */
class QuestionRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    /**
     * Query all records with related category and tags.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAllWithRelations(): QueryBuilder
    {
        return $this->createQueryBuilder('q')
            ->select('q', 'c', 't')
            ->join('q.category', 'c')
            ->leftJoin('q.tags', 't')
            ->orderBy('q.updatedAt', 'DESC');
    }
}
