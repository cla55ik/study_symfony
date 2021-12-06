<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Conference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    private const DAYS_BEFORE_REMOVE_SPAM = 1;
    private const DAYS_BEFORE_REMOVE_CONFIRM = 3;
    public const PAGINATOR_PER_PAGE = 2;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function getCommentPaginator(Conference $conference, int $offset):Paginator
    {
        $query = $this->createQueryBuilder('c')
            ->andWhere('c.conference = :conference')
            ->andWhere('c.state = :state')
            ->setParameter('conference', $conference)
            ->setParameter('state', 'published')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(self::PAGINATOR_PER_PAGE)
            ->setFirstResult($offset)
            ->getQuery()
            ;
        return new Paginator($query);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function countOldSpam():int
    {
        return $this->getOldSpam()
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function deleteOldSpam():int
    {
        return $this->getOldSpam()
            ->delete()->getQuery()->execute();
    }


    /**
     * @throws Exception
     */
    public function getOldSpam(): QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.state = :state_spam')
            ->andWhere('c.createdAt < :date')
            ->setParameters([
                'state_spam'=>'spam',
                'date' => new \DateTime(-self::DAYS_BEFORE_REMOVE_SPAM)
            ])
            ;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function countOldUnconfirmed():int
    {
        return $this->getOldUnconfirmed()
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @throws Exception
     */
    public function deleteOldUnconfirmed():int
    {
        return $this->getOldUnconfirmed()
            ->delete()
            ->getQuery()
            ->execute();
    }

    /**
     * @throws Exception
     */
    public function getOldUnconfirmed(): QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.state = :state_unconfirmed')
            ->andWhere('c.createdAt < :date')
            ->setParameters([
                'state_unconfirmed'=>'confirmation',
                'date'=> new \DateTime(-self::DAYS_BEFORE_REMOVE_CONFIRM)
            ])
            ;
    }



    // /**
    //  * @return Comment[] Returns an array of Comment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
