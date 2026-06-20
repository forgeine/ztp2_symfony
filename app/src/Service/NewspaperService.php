<?php

/**
 * Newspaper service.
 */

namespace App\Service;

use App\Dto\NewspaperListFiltersDto;
use App\Dto\NewspaperListInputFiltersDto;
use App\Entity\Comment;
use App\Entity\Enum\NewspaperStatus;
use App\Entity\Rating;
use App\Entity\Newspaper;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\RatingRepository;
use App\Repository\NewspaperRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class NewspaperService.
 */
class NewspaperService implements NewspaperServiceInterface
{
    /**
     * Items per page.
     *
     * @var int
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param CategoryServiceInterface $categoryService     Category Service
     * @param PaginatorInterface       $paginator           Paginator
     * @param TagServiceInterface      $tagService          Tag Service
     * @param CommentRepository        $commentRepository   Comment Repository
     * @param NewspaperRepository      $newspaperRepository Newspaper Repository
     * @param RatingRepository         $ratingRepository    Rating Repository
     * @param TagRepository            $tagRepository       Tag Repository
     */
    public function __construct(private readonly CategoryServiceInterface $categoryService, private readonly PaginatorInterface $paginator, private readonly TagServiceInterface $tagService, private readonly CommentRepository $commentRepository, private readonly NewspaperRepository $newspaperRepository, private readonly RatingRepository $ratingRepository, private readonly TagRepository $tagRepository)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int                          $page    Page
     * @param User|null                    $author  Author
     * @param NewspaperListInputFiltersDto $filters Filters
     *
     * @return PaginationInterface Pagination
     *
     * @throws NonUniqueResultException
     */
    public function getPaginatedList(int $page, ?User $author, NewspaperListInputFiltersDto $filters): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);
        if (null === $author) {
            return $this->paginator->paginate(
                $this->newspaperRepository->queryAll($filters),
                $page,
                self::PAGINATOR_ITEMS_PER_PAGE
            );
        }

        return $this->paginator->paginate(
            $this->newspaperRepository->queryByAuthor($author, $filters),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param Newspaper $newspaper Entity Newspaper
     *
     * @return void Void
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Newspaper $newspaper): void
    {
        $this->newspaperRepository->save($newspaper);
    }

    /**
     * Delete entity.
     *
     * @param Newspaper $newspaper Entity Newspaper
     *
     * @return void Void
     */
    public function delete(Newspaper $newspaper): void
    {
        $this->newspaperRepository->delete($newspaper);
    }

    /**
     * Find by title.
     *
     * @param string $title Title
     *
     * @return Tag|null Tag
     */
    public function findOneByTitle(string $title): ?Tag
    {
        return $this->tagRepository->findOneByTitle($title);
    }

    /**
     * Save comment.
     *
     * @param Comment $comment Entity Comment
     *
     * @return void Void
     */
    public function saveComment(Comment $comment): void
    {
        $this->commentRepository->save($comment);
    }

    /**
     * Delete comment.
     *
     * @param Comment $comment Entity Comment
     *
     * @return void Void
     */
    public function deleteComment(Comment $comment): void
    {
        $this->commentRepository->delete($comment);
    }

    /**
     * Save rating.
     *
     * @param Rating $rating Entity Rating
     *
     * @return void Void
     */
    public function saveRating(Rating $rating): void
    {
        $this->ratingRepository->save($rating);
    }

    /**
     * Prepare filters for list.
     *
     * @param NewspaperListInputFiltersDto $filters Filters
     *
     * @return NewspaperListFiltersDto Newspaper List Filters Dto
     *
     * @throws NonUniqueResultException
     */
    private function prepareFilters(NewspaperListInputFiltersDto $filters): NewspaperListFiltersDto
    {
        return new NewspaperListFiltersDto(
            null !== $filters->categoryId ? $this->categoryService->findOneById($filters->categoryId) : null,
            null !== $filters->tagId ? $this->tagService->findOneById($filters->tagId) : null,
            NewspaperStatus::tryFrom($filters->statusId) ?? NewspaperStatus::ACTIVE
        );
    }
}
