<?php

namespace App\Repository;

use App\Entity\Afspraak;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Afspraak>
 *
 * @method Afspraak|null find($id, $lockMode = null, $lockVersion = null)
 * @method Afspraak|null findOneBy(array $criteria, array $orderBy = null)
 * @method Afspraak[]    findAll()
 * @method Afspraak[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AfspraakRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Afspraak::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Afspraak $entity, bool $flush = true): void
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
    public function remove(Afspraak $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return Afspraak[] Returns an array of Afspraak objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Afspraak
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findMedewerkerAfspraken($value, $var = true): ?array
    {
        $qb = $this->createQueryBuilder('a')->where('a.medewerker IS NOT null');

        if($var) {
            $qb->andWhere('a.medewerker = :val');
        } else {
            $qb->andWhere('a.medewerker != :val');
        }

        return $qb->setParameter('val', $value)
            ->getQuery()
            ->getResult()
        ;
    }
}
