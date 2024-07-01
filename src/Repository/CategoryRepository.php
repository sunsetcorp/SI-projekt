<?php

/**
 * Category repository.
 */

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Query all records.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->select('partial category.{id, createdAt, updatedAt, title}')
            ->orderBy('category.updatedAt', 'DESC');
    }


    /**
         * Save entity.
         *
         * @param Category $category Category entity
         */
    public function save(Category $category): void
    {
        $category->setCreatedAt(new \DateTimeImmutable());
        $category->setUpdatedAt(new \DateTimeImmutable());
        if (null !== $category->getId()) {
            $category->setUpdatedAt(new \DateTimeImmutable());
        }
        $this->_em->persist($category);
        $this->_em->flush();
    }

    /**
     * Delete category entity.
     *
     * @param Category $category The category entity to delete
     */
    public function delete(Category $category): void
    {
        $this->_em->remove($category);
        $this->_em->flush();
    }

    /**
     * Can Category be deleted?
     *
     * @param Category $category Category entity
     *
     * @return bool Result
     */
    public function canBeDeleted(Category $category): bool
    {
        try {
            $result = $this->_em->createQueryBuilder()
                ->select('count(a.id)')
                ->from('App\Entity\Album', 'a')
                ->where('a.category = :category')
                ->setParameter('category', $category)
                ->getQuery()
                ->getSingleScalarResult();

            return $result === 0;
        } catch (NoResultException | NonUniqueResultException $exception) {
            return false;
        }
    }

    /**
     * Get or create new query builder.
     *
     * @param QueryBuilder|null $queryBuilder Query builder
     *
     * @return QueryBuilder Query builder
     */
    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('category');
    }
}
