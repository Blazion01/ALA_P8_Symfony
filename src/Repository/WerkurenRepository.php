<?php

namespace App\Repository;

use App\Entity\Werkuren;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Werkuren>
 *
 * @method Werkuren|null find($id, $lockMode = null, $lockVersion = null)
 * @method Werkuren|null findOneBy(array $criteria, array $orderBy = null)
 * @method Werkuren[]    findAll()
 * @method Werkuren[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WerkurenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Werkuren::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Werkuren $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Werkuren $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return Werkuren[] Returns an array of Werkuren objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Werkuren
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
