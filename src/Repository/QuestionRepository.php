<?php

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Question|null find($id, $lockMode = null, $lockVersion = null)
 * @method Question|null findOneBy(array $criteria, array $orderBy = null)
 * @method Question[]    findAll()
 * @method Question[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Question::class);
    }

    //Pour la démo du queryBuilder
    public function findListQuestionsQB()
    {
        //Le query builder sait que l'on utilise l'entity Question
        //car on est dans le QuestionRepository
        $qd = $this->createQueryBuilder('q');
        //Pour lui dire l'entity que l'on veut utiliser (même si c'est sous-entendue)
        //$qd->from(Question::class);
        $qd->andWhere('q.status = :status')
            ->orderBy('q.creationDate', 'DESC')
            ->join('q.subjects', 's')
            ->addSelect('s')
            ->setParameter(':status', 'debating')
            ->setFirstResult(200)
            ->setFirstResult(0);

        $query = $qd->getQuery();
        $questions = $query->getResult();
        return $questions;
    }

    public function findListQuestions()
    {
        $dql = "SELECT q, s
                FROM App\Entity\Question q
                JOIN q.subjects s
                WHERE q.status = :status
                ORDER BY q.supports DESC";

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setMaxResults(200);
        $query->setFirstResult(0);
        $query->setParameter(':status', 'debating');
        $questions = $query->getResult();

        return $questions;
    }

    // /**
    //  * @return Question[] Returns an array of Question objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('q.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Question
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
