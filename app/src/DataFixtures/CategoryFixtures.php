<?php
/**
 * Category fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;

/**
 * Class CategoryFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class CategoryFixtures extends AbstractBaseFixtures
{
    /**
     * Number of categories.
     */
    private const CATEGORY_COUNT = 20;

    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        $this->createMany(self::CATEGORY_COUNT, 'categories', function (int $i) {
            $category = new Category();
            $category->setTitle($this->getGeneratedTitle(64));
            $category->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $category->setUpdatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );

            return $category;
        });
        $this->manager->flush();
    }

    /**
     * Get generated title.
     *
     * @param int $maxLength Max length
     *
     * @return string Generated title
     */
    private function getGeneratedTitle(int $maxLength): string
    {
        do {
            $title = $this->faker->unique()->words($this->faker->numberBetween(1, 2), true);
            $title = trim((string) $title);
        } while (strlen($title) > $maxLength);

        return ucfirst($title);
    }
}
