<?php

/**
 * Newspaper service interface.
 */

namespace App\Service;

use App\Dto\NewspaperListInputFiltersDto;
use App\Entity\Comment;
use App\Entity\Newspaper;
use App\Entity\Rating;
use App\Entity\Tag;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface NewspaperServiceInterface.
 */
interface NewspaperServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int                          $page    Page
     * @param User|null                    $author  Author
     * @param NewspaperListInputFiltersDto $filters Filters
     *
     * @return PaginationInterface Pagination
     */
    public function getPaginatedList(int $page, ?User $author, NewspaperListInputFiltersDto $filters): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Newspaper $newspaper Newspaper entity
     */
    public function save(Newspaper $newspaper): void;

    /**
     * Delete entity.
     *
     * @param Newspaper $newspaper Newspaper entity
     */
    public function delete(Newspaper $newspaper): void;

    /**
     * Find one by title.
     *
     * @param string $title title
     *
     * @return Tag|null Tag
     */
    public function findOneByTitle(string $title): ?Tag;

    /**
     * Save comment.
     *
     * @param Comment $comment Entity comment
     *
     * @return void Void
     */
    public function saveComment(Comment $comment): void;

    /**
     * Delete comment.
     *
     * @param Comment $comment Entity comment
     *
     * @return void Void
     */
    public function deleteComment(Comment $comment): void;

    /**
     * Save rating.
     *
     * @param Rating $rating Entity rating
     *
     * @return void Void
     */
    public function saveRating(Rating $rating): void;
}
