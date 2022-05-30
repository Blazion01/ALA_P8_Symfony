<?php

namespace App\Repository;

use App\Entity\Medewerker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @extends ServiceEntityRepository<Medewerker>
 *
 * @method Medewerker|null find($id, $lockMode = null, $lockVersion = null)
 * @method Medewerker|null findOneBy(array $criteria, array $orderBy = null)
 * @method Medewerker[]    findAll()
 * @method Medewerker[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MedewerkerRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(Security $security, ManagerRegistry $registry)
    {
        parent::__construct($registry, Medewerker::class);
        $this->security = $security;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Medewerker $entity, bool $flush = true): void
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
    public function remove(Medewerker $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Medewerker) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    // /**
    //  * @return Medewerker[] Returns an array of Medewerker objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Medewerker
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    
    public function findAllWhere($col, $val, $id = null): ?Medewerker
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.:col = :val')
            ->setParameter('val', $val)
            ->setParameter('col', $col)
        ;

        if (!$this->security->isGranted('ROLE_CUSTOMER')) {
            $qb->addWhere('m.id != :id')
               ->setParameter('id', $id);
        }

        return $qb;
    }

    /**
     * @param string|null $value
     */
    public function findAllExcept($val)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.id != :val')
            ->setParameter('val', $val)
        ;
    }

    /**
     * @param string|null $value
     */
    public function getUniqueValues($col)
    {
        return $this->createQueryBuilder(`m.:col`)
            ->setParameter('col', $col)
            ->distinct();
        ;
    }
}
