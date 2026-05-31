<?php
/**
 * Category service interface.
 */

namespace App\Service;

use App\Entity\Category;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface CategoryServiceInterface.
 */
interface CategoryServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Category $category Entity Category
     *
     * @return void Void
     */
    public function save(Category $category): void;

    /**
     * Delete entity.
     *
     * @param Category $category Entity Category
     *
     * @return void Void
     */
    public function delete(Category $category): void;

    /**
     * Can Category be deleted?
     *
     * @param Category $category Entity Category
     *
     * @return bool Result
     */
    public function canBeDeleted(Category $category): bool;

    /**
     * Find one by id.
     *
     * @param int $id Category id
     *
     * @return Category|null Category
     */
    public function findOneById(int $id): ?Category;
}
