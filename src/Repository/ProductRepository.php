<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function getProductsList(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.id', 'p.name', 'p.description', 'p.price', 'b.name as brandName')
            ->leftjoin('p.brand', 'b')
            ->getQuery();

        return $qb->getArrayResult();
    }

    public function findProductsByBrand(string $brandName): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.id', 'p.name', 'p.description', 'p.price', 'b.name as brandName')
            ->join('p.brand', 'b')
            ->where('b.name = :brandName')
            ->setParameter('brandName', $brandName)
            ->getQuery();

        return $qb->getArrayResult();
    }

    // Tìm kiếm sản phẩm theo giá
    public function findProductsByPrice(float $price): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.id', 'p.name', 'p.description', 'p.price', 'b.name as brandName')
            ->join('p.brand', 'b')
            ->where('p.price < :price')
            ->setParameter('price', $price)
            ->getQuery();

        return $qb->getArrayResult();
    }

    public function findProductById(int $id): ?array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.id', 'p.name', 'p.description', 'p.price', 'b.name as brandName')
            ->join('p.brand', 'b')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery();

        return $qb->getOneOrNullResult();
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
