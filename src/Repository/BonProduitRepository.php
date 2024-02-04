<?php

namespace App\Repository;

use App\Entity\BonProduit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BonProduit>
 *
 * @method BonProduit|null find($id, $lockMode = null, $lockVersion = null)
 * @method BonProduit|null findOneBy(array $criteria, array $orderBy = null)
 * @method BonProduit[]    findAll()
 * @method BonProduit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BonProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BonProduit::class);
    }

//    /**
//     * @return BonProduit[] Returns an array of BonProduit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?BonProduit
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
