<?php

/**
 * Album repository.
 */

namespace App\Repository;

use App\Entity\Album;
use App\Entity\Category;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

/**
 *  Class AlbumRepository.
 *
 * @method Album|null find($id, $lockMode = null, $lockVersion = null)
 * @method Album|null findOneBy(array $criteria, array $orderBy = null)
 * @method Album[]    findAll()
 * @method Album[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Album>
 *
 * @psalm-suppress LessSpecificImplementedReturnType
 */
class AlbumRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Album::class);
    }


    /**
     * Query all albums.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->orderBy('album.releaseDate', 'DESC');
    }




    /**
     * Find paginated albums by category ID.
     *
     * @param int $categoryId Category ID
     *
     * @return \Doctrine\ORM\QueryBuilder Query builder
     */
    public function findPaginatedByCategory(int $categoryId): \Doctrine\ORM\QueryBuilder
    {
        $qb = $this->createQueryBuilder('album')
            ->leftJoin('album.category', 'category')
            ->andWhere('category.id = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->orderBy('album.releaseDate', 'DESC');

        return $qb;
    }

    /**
     * Find albums by tag.
     *
     * @param Tag $tag
     *
     * @return Album[]
     */
    public function findByTag(Tag $tag): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.tags', 't')
            ->where('t.id = :tag')
            ->setParameter('tag', $tag->getId())
            ->getQuery('a.releaseDate', 'DESC')
            ->getResult();
    }

    /**
     * Count albums by category.
     *
     * @param Category $category Category entity
     *
     * @return int Number of albums in category
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countByCategory(Category $category): int
    {
        $qb = $this->createQueryBuilder('album');

        return (int) $qb->select($qb->expr()->countDistinct('album.id'))
            ->where('album.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count albums by tag.
     *
     * @param Tag $tag Tag entity
     *
     * @return int Number of albums in category
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countByTag(Tag $tag): int
    {
        $qb = $this->createQueryBuilder('album');

        return (int) $qb->select($qb->expr()->countDistinct('album.id'))
            ->innerJoin('album.tags', 't')
            ->where('t.id = :tagId')
            ->setParameter('tagId', $tag->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Save entity.
     *
     * @param Album $album Album entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Album $album): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->persist($album);
        $this->_em->flush();
    }

    /**
     * Delete entity.
     *
     * @param Album $album Album entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(Album $album): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->remove($album);
        $this->_em->flush();
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
        return $queryBuilder ?? $this->createQueryBuilder('album');
    }
}
