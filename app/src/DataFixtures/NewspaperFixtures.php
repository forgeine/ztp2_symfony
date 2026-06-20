<?php

/**
 * Newspaper fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Newspaper;
use App\Entity\Rating;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

/**
 * Class NewspaperFixtures.
 */
class NewspaperFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Number of newspapers.
     */
    private const NEWSPAPER_COUNT = 100;

    /**
     * Number of comments per newspaper.
     */
    private const COMMENTS_PER_NEWSPAPER = 2;

    /**
     * Number of ratings per newspaper.
     */
    private const RATINGS_PER_NEWSPAPER = 3;

    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullPropertyFetch
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        if (!$this->manager instanceof ObjectManager || !$this->faker instanceof Generator) {
            return;
        }
        $this->createMany(self::NEWSPAPER_COUNT, 'newspapers', function (int $i) {
            $createdAt = \DateTimeImmutable::createFromMutable(
                $this->faker->dateTimeBetween('-100 days', '-10 days')
            );
            $updatedAt = $createdAt->modify(sprintf('+%d days', $this->faker->numberBetween(1, 9)));
            $newspaper = new Newspaper();
            $newspaper->setTitle($this->getGeneratedTitle());
            $newspaper->setContent($this->getGeneratedContent());
            $newspaper->setCreatedAt($createdAt);
            $newspaper->setUpdatedAt($updatedAt);
            $category = $this->getRandomReference('categories');
            $newspaper->setCategory($category);
            $tags = $this->getRandomReferences(
                'tags',
                $this->faker->numberBetween(2, 5)
            );
            foreach ($tags as $tag) {
                $newspaper->addTag($tag);
            }
            $author = $this->getRandomReference('users');
            $newspaper->setAuthor($author);
            $this->addComments($newspaper, $createdAt);
            $this->addRatings($newspaper);
            $newspaper->calculateAverageRating();

            return $newspaper;
        });
        $this->manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     *
     * @psalm-return array{0: CategoryFixtures::class, 1: TagFixtures::class, 2: UserFixtures::class}
     */
    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            TagFixtures::class,
            UserFixtures::class,
        ];
    }

    /**
     * Get generated title.
     *
     * @return string Title
     */
    private function getGeneratedTitle(): string
    {
        $title = $this->faker->unique()->realText(90);
        $title = preg_replace('/\s+/', ' ', $title);
        $title = trim((string) $title, " \t\n\r\0\x0B.,;:!?\"'");

        return ucfirst($title);
    }

    /**
     * Get generated content.
     *
     * @return string Content
     */
    private function getGeneratedContent(): string
    {
        $content = $this->faker->realText(700);
        $content = preg_replace('/\s+/', ' ', $content);

        return trim((string) $content);
    }

    /**
     * Add generated comments.
     *
     * @param Newspaper          $newspaper Newspaper entity
     * @param \DateTimeImmutable $createdAt Created at
     */
    private function addComments(Newspaper $newspaper, \DateTimeImmutable $createdAt): void
    {
        for ($i = 0; $i < self::COMMENTS_PER_NEWSPAPER; ++$i) {
            $comment = new Comment();
            $comment->setNewspaper($newspaper);
            $comment->setAuthor($this->getRandomReference('users'));
            $comment->setContent($this->getGeneratedCommentContent());
            $comment->setCreatedAt($createdAt->modify(sprintf('+%d hours', $this->faker->numberBetween(1, 72))));
            $newspaper->addComment($comment);
            $this->manager->persist($comment);
        }
    }

    /**
     * Add generated ratings.
     *
     * @param Newspaper $newspaper Newspaper entity
     */
    private function addRatings(Newspaper $newspaper): void
    {
        for ($i = 0; $i < self::RATINGS_PER_NEWSPAPER; ++$i) {
            $rating = new Rating();
            $rating->setNewspaper($newspaper);
            $rating->setUser($this->getRandomReference('users'));
            $rating->setValue($this->faker->numberBetween(1, 5));
            $newspaper->addRating($rating);
            $this->manager->persist($rating);
        }
    }

    /**
     * Get generated comment content.
     *
     * @return string Content
     */
    private function getGeneratedCommentContent(): string
    {
        $content = $this->faker->realText(160);
        $content = preg_replace('/\s+/', ' ', $content);

        return trim((string) $content);
    }
}
