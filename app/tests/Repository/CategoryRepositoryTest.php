<?php

/**
 * Category repository tests.
 */

namespace App\Tests\Repository;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CategoryRepositoryTest.
 */
class CategoryRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private CategoryRepository $categoryRepository;

    /**
     * Set up test.
     */
    protected function setUp(): void
    {
        static::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->categoryRepository = static::getContainer()->get(CategoryRepository::class);
    }

    /**
     * Test save, query all and delete.
     */
    public function testSaveQueryAllAndDelete(): void
    {
        // given
        $category = new Category();
        $category->setTitle('Test Category Repository');

        // when
        $this->categoryRepository->save($category);
        $result = $this->categoryRepository->queryAll()->getQuery()->getResult();

        // then
        self::assertContains($category, $result);

        // when
        $categoryId = $category->getId();
        $this->categoryRepository->delete($category);

        // then
        self::assertNull($this->categoryRepository->find($categoryId));
    }
}
