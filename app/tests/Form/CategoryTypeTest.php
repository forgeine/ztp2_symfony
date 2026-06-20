<?php

/**
 * Category form tests.
 */

namespace App\Tests\Form;

use App\Entity\Category;
use App\Form\Type\CategoryType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Class CategoryTypeTest.
 */
class CategoryTypeTest extends TypeTestCase
{
    /**
     * Test submit valid data.
     */
    public function testSubmitValidData(): void
    {
        // given
        $category = new Category();
        $form = $this->factory->create(CategoryType::class, $category);

        // when
        $form->submit(['title' => 'Test Category Form']);

        // then
        self::assertTrue($form->isSynchronized());
        self::assertSame('Test Category Form', $category->getTitle());
        self::assertSame(Category::class, $form->getConfig()->getDataClass());
        self::assertSame('category', $form->getName());
    }
}
