<?php

/**
 * Newspaper list filters DTO.
 */

namespace App\Dto;

use App\Entity\Category;
use App\Entity\Enum\NewspaperStatus;
use App\Entity\Tag;

/**
 * Class NewspaperListFiltersDto.
 */
class NewspaperListFiltersDto
{
    /**
     * Constructor.
     *
     * @param Category|null   $category        Category entity
     * @param Tag|null        $tag             Tag entity
     * @param NewspaperStatus $newspaperStatus Newspaper status
     */
    public function __construct(public readonly ?Category $category, public readonly ?Tag $tag, public readonly NewspaperStatus $newspaperStatus)
    {
    }
}
