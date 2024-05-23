<?php
/**
 * Answer repository.
 */

namespace App\Repository;

use App\Entity\Answer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class AnswerRepository.
 *
 * @extends ServiceEntityRepository<Answer>
 */
class AnswerRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Answer::class);
    }

    /**
     * Save an answer entity.
     *
     * @param Answer $answer Answer entity
     * @param bool   $flush  Whether to flush changes (default: false)
     */
    public function save(Answer $answer, bool $flush = false): void
    {
        $this->getEntityManager()->persist($answer);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove an answer entity.
     *
     * @param Answer $answer Answer entity
     * @param bool   $flush  Whether to flush changes (default: false)
     */
    public function remove(Answer $answer, bool $flush = false): void
    {
        $this->getEntityManager()->remove($answer);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return Answer[] Returns an array of Answer objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Answer
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
