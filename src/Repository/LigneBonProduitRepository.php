<?php

namespace App\Repository;

use App\Entity\LigneBonProduit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LigneBonProduit>
 *
 * @method LigneBonProduit|null find($id, $lockMode = null, $lockVersion = null)
 * @method LigneBonProduit|null findOneBy(array $criteria, array $orderBy = null)
 * @method LigneBonProduit[]    findAll()
 * @method LigneBonProduit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LigneBonProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LigneBonProduit::class);
    }

//    /**
//     * @return LigneBonProduit[] Returns an array of LigneBonProduit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?LigneBonProduit
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
