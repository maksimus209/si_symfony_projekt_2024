<?php
/**
 * Question repository.
 */

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class QuestionRepository.
 *
 * @method Question|null find($id, $lockMode = null, $lockVersion = null)
 * @method Question|null findOneBy(array $criteria, array $orderBy = null)
 * @method Question[]    findAll()
 * @method Question[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Question>
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
     * Find questions by category.
     *
     * @param Category $category The category to filter questions by
     *
     * @return Question[] Returns an array of Question objects
     */
    public function findByCategory(Category $category): array
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.category = :category')
            ->setParameter('category', $category)
            ->orderBy('q.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Query all records.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->select(
                'partial question.{id, createdAt, updatedAt, title}',
                'partial category.{id, title}'
            )
            ->join('question.category', 'category')
            ->orderBy('question.updatedAt', 'DESC');
    }

    /**
     * Count questions by category.
     *
     * @param Category $category Category
     *
     * @return int Number of questions in category
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countByCategory(Category $category): int
    {
        $qb = $this->getOrCreateQueryBuilder();

        return $qb->select($qb->expr()->countDistinct('question.id'))
            ->where('question.category = :category')
            ->setParameter(':category', $category)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Save entity.
     *
     * @param Question $question Question entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Question $question): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->persist($question);
        $this->_em->flush();
    }

    /**
     * Delete entity.
     *
     * @param Question $question Question entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(Question $question): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->remove($question);
        $this->_em->flush();
    }

    /**
     * Get or create new query builder.
     *
     * @param QueryBuilder|null $queryBuilder Query builder
     *
     * @return QueryBuilder Query builder
     */
    private function getOrCreateQueryBuilder(?QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('question');
    }
}
