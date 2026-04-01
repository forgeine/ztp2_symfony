<?php
namespace App\Service;

use App\Entity\Category;

/**
 * Interface Ca.
 */
interface CategoryServiceInterface
{
    /**
     * Save entity.
     *
     * @param Category $category Category entity
     */
    public function save(Category $category): void;

}
