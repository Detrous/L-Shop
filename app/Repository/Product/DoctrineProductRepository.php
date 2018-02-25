<?php
declare(strict_types = 1);

namespace App\Repository\Product;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use LaravelDoctrine\ORM\Pagination\PaginatesFromRequest;

class DoctrineProductRepository implements ProductRepository
{
    use PaginatesFromRequest;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var EntityRepository
     */
    private $er;

    public function __construct(EntityManagerInterface $em, EntityRepository $er)
    {
        $this->em = $em;
        $this->er = $er;
    }

    public function create(Product $product): void
    {
        $this->em->persist($product);
        $this->em->flush();
    }

    public function deleteAll(): bool
    {
        return (bool)$this->er->createQueryBuilder('p')
            ->delete()
            ->getQuery()
            ->getResult();
    }

    public function find(int $id): ?Product
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->er->find($id);
    }

    public function findForCategoryPaginated(Category $category, string $orderBy, bool $descending, int $perPage): LengthAwarePaginator
    {
        return $this->paginate(
            $this->createQueryBuilder('product')
                ->join('product.item', 'item')
                ->where('product.category = :category')
                ->orderBy($orderBy, $descending ? 'DESC' : 'ASC')
                ->setParameter('category', $category)
                ->getQuery(),
            $perPage,
            'page',
            false
        );
    }

    public function findPaginated(int $perPage): LengthAwarePaginator
    {
        return $this->paginateAll($perPage, 'page');
    }

    public function findPaginatedWithOrder(string $orderBy, bool $descending, int $perPage): LengthAwarePaginator
    {
        return $this->paginate(
            $this->createQueryBuilder('product')
                ->join('product.item', 'item')
                ->orderBy($orderBy, $descending ? 'DESC' : 'ASC')
                ->getQuery(),
            $perPage,
            'page',
            false
        );
    }

    public function findPaginateWithSearch(string $search, int $perPage): LengthAwarePaginator
    {
        return $this->paginate(
            $this->createQueryBuilder('p')
                ->join('p.item', 'i')
                ->where('p.id LIKE :search')
                ->orWhere('p.price LIKE :search')
                ->orWhere('p.stack LIKE :search')
                ->orWhere('i.id LIKE :search')
                ->orWhere('i.name LIKE :search')
                ->setParameter('search', "%{$search}%")
                ->getQuery(),
            $perPage,
            'page',
            false
        );
    }

    public function findPaginatedWithOrderAndSearch(string $orderBy, bool $descending, string $search, int $perPage): LengthAwarePaginator
    {
        return $this->paginate(
            $this->createQueryBuilder('product')
                ->orderBy($orderBy, $descending ? 'DESC' : 'ASC')
                ->join('product.item', 'item')
                ->where('product.id LIKE :search')
                ->orWhere('product.price LIKE :search')
                ->orWhere('product.stack LIKE :search')
                ->orWhere('item.id LIKE :search')
                ->orWhere('item.name LIKE :search')
                ->setParameter('search', "%{$search}%")
                ->getQuery(),
            $perPage,
            'page',
            false
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createQueryBuilder($alias, $indexBy = null)
    {
        return $this->er->createQueryBuilder($alias, $indexBy);
    }
}
