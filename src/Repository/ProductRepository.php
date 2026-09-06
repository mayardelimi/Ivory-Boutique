<?php
// src/Repository/ProductRepository.php
namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @param array{category?: string, color?: string, size?: string, maxPrice?: float, keywords?: string} $filters
     */
    public function findByFilters(array $filters, int $limit = 5): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.variants', 'v')
            ->andWhere('v.quantity_available > 0')
            ->distinct();

        if (!empty($filters['category'])) {
            $qb->andWhere('LOWER(c.name) = LOWER(:category)')
                ->setParameter('category', $filters['category']);
        }

        if (!empty($filters['color'])) {
            $qb->andWhere('LOWER(v.color) = LOWER(:color)')
                ->setParameter('color', $filters['color']);
        }

        if (!empty($filters['size'])) {
            $qb->andWhere('LOWER(v.size) = LOWER(:size)')
                ->setParameter('size', $filters['size']);
        }

        if (!empty($filters['keywords'])) {
            $qb->andWhere('p.name LIKE :kw OR p.description LIKE :kw')
                ->setParameter('kw', '%'.$filters['keywords'].'%');
        }

        /** @var Product[] $products */
        $products = $qb->getQuery()->getResult();

        if (!empty($filters['maxPrice'])) {
            $products = array_values(array_filter(
                $products,
                fn(Product $p) => $p->getPrice() !== null && $p->getPrice() <= $filters['maxPrice']
            ));
        }

        return array_slice($products, 0, $limit);
    }


    public function getDistinctCategoryNames(): array
    {
        return array_column(
            $this->createQueryBuilder('p')
                ->select('DISTINCT c.name')
                ->leftJoin('p.category', 'c')
                ->getQuery()
                ->getScalarResult(),
            'name'
        );
    }
}
