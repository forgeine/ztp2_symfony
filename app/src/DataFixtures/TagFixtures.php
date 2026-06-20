<?php

/**
 * Tag fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Tag;

/**
 * Class TagFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class TagFixtures extends AbstractBaseFixtures
{
    /**
     * Number of tags.
     */
    private const TAG_COUNT = 20;

    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        $this->createMany(self::TAG_COUNT, 'tags', function (int $i) {
            $tag = new Tag();
            $tag->setTitle($this->getGeneratedTitle(64));
            $tag->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $tag->setUpdatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );

            return $tag;
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
