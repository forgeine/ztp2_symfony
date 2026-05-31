<?php
/**
 * Base fixtures.
 */

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

/**
 * Class AbstractBaseFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
abstract class AbstractBaseFixtures extends Fixture
{
    /**
     * Faker.
     */
    protected ?Generator $faker = null;

    /**
     * Persistence object manager.
     */
    protected ?ObjectManager $manager = null;

    /**
     * @var array<string, array<int, array{name: string, class: string}>>
     */
    private static array $references = [];

    /**
     * Load.
     *
     * @param ObjectManager $manager Persistence object manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->faker = Factory::create();
        $this->loadData();
    }

    /**
     * Load data.
     */
    abstract protected function loadData(): void;

    /**
     * Create many objects at once:.
     *
     *      $this->createMany(10, function(int $i) {
     *          $user = new User();
     *          $user->setFirstName('Ryan');
     *
     *           return $user;
     *      });
     *
     * @param int      $count     Number of object to create
     * @param string   $groupName Tag these created objects with this group name,
     *                            and use this later with getRandomReference(s)
     *                            to fetch only from this specific group
     * @param callable $factory   Defines method of creating objects
     *
     * @psalm-suppress PossiblyNullReference
     */
    protected function createMany(int $count, string $groupName, callable $factory): void
    {
        for ($i = 0; $i < $count; ++$i) {
            /** @var object|null $entity */
            $entity = $factory($i);

            if (null === $entity) {
                throw new \LogicException(
                    'Did you forget to return the entity object from your callback?'
                );
            }

            $this->manager->persist($entity);

            $referenceName = sprintf('%s_%d', $groupName, $i);

            $this->addReference($referenceName, $entity);

            self::$references[$groupName][] = [
                'name' => $referenceName,
                'class' => $entity::class,
            ];
        }
    }

    /**
     * Set random reference to the object.
     *
     * @param string $groupName Objects group name
     *
     * @return object Random object reference
     *
     * @psalm-suppress MixedAssignment
     * @psalm-suppress UnusedForeachValue
     */
    protected function getRandomReference(string $groupName): object
    {
        if (!$this->faker instanceof Generator) {
            throw new \LogicException('Faker is not initialized.');
        }

        if (empty(self::$references[$groupName])) {
            throw new \InvalidArgumentException(sprintf(
                'Did not find any references saved with the group name "%s"',
                $groupName
            ));
        }

        $randomReference = $this->faker->randomElement(
            self::$references[$groupName]
        );

        return $this->getReference(
            $randomReference['name'],
            $randomReference['class']
        );
    }

    /**
     * Get array of objects references based on count.
     *
     * @param string $groupName Object group name
     * @param int    $count     Number of references
     *
     * @return object[] Result
     *
     * @psalm-return list<object>
     */
    protected function getRandomReferences(string $groupName, int $count): array
    {
        $references = [];
        while (count($references) < $count) {
            $references[] = $this->getRandomReference($groupName);
        }

        return $references;
    }
}
