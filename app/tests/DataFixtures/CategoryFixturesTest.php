<?php

/**
 * Category fixtures tests.
 */

namespace App\Tests\DataFixtures;

use App\DataFixtures\CategoryFixtures;
use App\Entity\Category;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CategoryFixturesTest.
 */
class CategoryFixturesTest extends KernelTestCase
{
    /**
     * Test load.
     */
    public function testLoad(): void
    {
        // given
        static::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $fixtures = new CategoryFixtures();
        $fixtures->setReferenceRepository(new ReferenceRepository($entityManager));

        // when
        $fixtures->load($entityManager);

        // then
        $categories = $entityManager->getRepository(Category::class)->findAll();
        self::assertCount(20, $categories);
    }
}
