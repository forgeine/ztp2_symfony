<?php
/**
 * Newspaper repository.
 */

namespace App\Repository;

use App\Dto\NewspaperListFiltersDto;
use App\Entity\Category;
use App\Entity\Newspaper;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class NewspaperRepository.
 *
 * @method Newspaper|null find($id, $lockMode = null, $lockVersion = null)
 * @method Newspaper|null findOneBy(array $criteria, array $orderBy = null)
 * @method Newspaper[]    findAll()
 * @method Newspaper[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Newspaper>
 *
 * @psalm-suppress LessSpecificImplementedReturnType
 */
class NewspaperRepository extends ServiceEntityRepository
{
    /**
     * Paginator items.
     */
    public const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry ManagerRegistry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Newspaper::class);
    }

    /**
     * QueryAll.
     *
     * @param NewspaperListFiltersDto $filters NewspaperListFiltersDto
     *
     * @return QueryBuilder Aply filters
     */
    public function queryAll(NewspaperListFiltersDto $filters): QueryBuilder
    {
        $queryBuilder = $this->getOrCreateQueryBuilder()
            ->select(
                'partial newspaper.{id, createdAt, updatedAt, title, content, averageRating}',
                'partial category.{id, title}',
                'partial tags.{id, title}',
                'partial author.{id, email}'
            )
            ->join('newspaper.category', 'category')
            ->leftJoin('newspaper.tags', 'tags')
            ->leftJoin('newspaper.author', 'author')
            ->orderBy('newspaper.updatedAt', 'DESC');

        return $this->applyFiltersToList($queryBuilder, $filters);
    }

    /**
     * Count by category.
     *
     * @param Category $category Entity
     *
     * @return int Count by category
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countByCategory(Category $category): int
    {
        $qb = $this->getOrCreateQueryBuilder();

        return $qb->select($qb->expr()->countDistinct('newspaper.id'))
            ->where('newspaper.category = :category')
            ->setParameter(':category', $category)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count by tag.
     *
     * @param Tag $tag Entity
     *
     * @return int Count by tag
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countByTag(Tag $tag): int
    {
        $qb = $this->getOrCreateQueryBuilder();

        return $qb->select($qb->expr()->countDistinct('newspaper.id'))
            ->join('newspaper.tags', 't')
            ->where('t = :tag')
            ->setParameter('tag', $tag)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Entity save.
     *
     * @param Newspaper $newspaper Entity
     *
     * @return void Void
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Newspaper $newspaper): void
    {
        $this->getEntityManager()->persist($newspaper);
        $this->getEntityManager()->flush();
    }

    /**
     * Entity delete.
     *
     * @param Newspaper $newspaper       Entity
     * @param bool      $cascadeComments Comments
     * @param bool      $cascadeRatings  Ratings
     */
    public function delete(Newspaper $newspaper, bool $cascadeComments = true, bool $cascadeRatings = true): void
    {
        $entityManager = $this->getEntityManager();
        if ($cascadeComments) {
            foreach ($newspaper->getComments() as $comment) {
                $entityManager->remove($comment);
            }
        }
        if ($cascadeRatings) {
            foreach ($newspaper->getRatings() as $rating) {
                $entityManager->remove($rating);
            }
        }
        $entityManager->remove($newspaper);
        $entityManager->flush();
    }

    /**
     * Query by author.
     *
     * @param UserInterface           $user    User
     * @param NewspaperListFiltersDto $filters Filters
     *
     * @return QueryBuilder QueryBuilder
     */
    public function queryByAuthor(UserInterface $user, NewspaperListFiltersDto $filters): QueryBuilder
    {
        $queryBuilder = $this->queryAll($filters);
        $queryBuilder->andWhere('newspaper.author = :author')
            ->setParameter('author', $user);

        return $queryBuilder;
    }

    /**
     * Get or create query builder.
     *
     * @param QueryBuilder|null $queryBuilder Query builder
     *
     * @return QueryBuilder Query builder for newspaper
     */
    private function getOrCreateQueryBuilder(?QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('newspaper');
    }

    /**
     * Apply filters to list.
     *
     * @param QueryBuilder            $queryBuilder QueryBuilder
     * @param NewspaperListFiltersDto $filters      Filters
     *
     * @return QueryBuilder Applying filters
     */
    private function applyFiltersToList(QueryBuilder $queryBuilder, NewspaperListFiltersDto $filters): QueryBuilder
    {
        if ($filters->category instanceof Category) {
            $queryBuilder->andWhere('category = :category')
                ->setParameter('category', $filters->category);
        }
        if ($filters->tag instanceof Tag) {
            $queryBuilder->andWhere('tags IN (:tag)')
                ->setParameter('tag', $filters->tag);
        }

        return $queryBuilder;
    }

    /**
     * Calculate average rating.
     *
     * @param Newspaper $newspaper Entity
     *
     * @return void Void
     */
    private function calculateAverageRating(Newspaper $newspaper): void
    {
        $entityManager = $this->getEntityManager();
        $sum = 0;
        $ratings = $newspaper->getRatings();
        $count = $ratings->count();
        if ($count > 0) {
            foreach ($ratings as $rating) {
                $sum += $rating->getValue();
            }
            $averageRating = $sum / $count;
        } else {
            $averageRating = null;
        }
        $newspaper->setAverageRating($averageRating);
        $entityManager->persist($newspaper);
        $entityManager->flush();
    }
}
