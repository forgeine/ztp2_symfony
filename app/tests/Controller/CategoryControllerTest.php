<?php

/**
 * Category controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Newspaper;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class CategoryControllerTest.
 */
class CategoryControllerTest extends WebTestCase
{
    /**
     * HTTP client.
     */
    private KernelBrowser $httpClient;

    /**
     * Entity manager.
     */
    private EntityManagerInterface $entityManager;

    /**
     * Set up test.
     */
    protected function setUp(): void
    {
        $this->httpClient = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * Test index action.
     */
    public function testIndex(): void
    {
        // given
        $category = $this->createCategory('Test Controller Category Index');

        // when
        $this->httpClient->request('GET', '/category?page=1');

        // then
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('table', (string) $category->getTitle());
    }

    /**
     * Test index action with an empty list.
     */
    public function testIndexWithEmptyList(): void
    {
        // given
        $expectedMessage = 'Lista jest pusta.';

        // when
        $this->httpClient->request('GET', '/category');

        // then
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.alert', $expectedMessage);
    }

    /**
     * Test show action.
     */
    public function testShow(): void
    {
        // given
        $category = $this->createCategory('Test Controller Category Show');

        // when
        $crawler = $this->httpClient->request('GET', '/category/'.$category->getId());

        // then
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', (string) $category->getTitle());
        self::assertCount(1, $crawler->filter('h1'));
    }

    /**
     * Test create action for an anonymous user.
     */
    public function testCreateRedirectsAnonymousUser(): void
    {
        // given
        $expectedLocation = '/category';

        // when
        $this->httpClient->request('GET', '/category/create');

        // then
        self::assertResponseRedirects($expectedLocation);
    }

    /**
     * Test create action for an administrator.
     */
    public function testCreate(): void
    {
        // given
        $categoryTitle = 'Test Controller Category Create';
        $this->httpClient->loginUser($this->createAdmin());
        $crawler = $this->httpClient->request('GET', '/category/create');
        $form = $crawler->filter('form')->form([
            'category[title]' => $categoryTitle,
        ]);

        // when
        $this->httpClient->submit($form);

        // then
        self::assertResponseRedirects('/category');
        $category = $this->getEntityManager()
            ->getRepository(Category::class)
            ->findOneBy(['title' => $categoryTitle]);
        self::assertInstanceOf(Category::class, $category);
    }

    /**
     * Test create action with invalid data.
     */
    public function testCreateWithInvalidData(): void
    {
        // given
        $this->httpClient->loginUser($this->createAdmin());
        $crawler = $this->httpClient->request('GET', '/category/create');
        $form = $crawler->filter('form')->form([
            'category[title]' => '',
        ]);

        // when
        $this->httpClient->submit($form);

        // then
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.alert-danger');
    }

    /**
     * Test edit action for an anonymous user.
     */
    public function testEditRedirectsAnonymousUser(): void
    {
        // given
        $category = $this->createCategory('Test Controller Category Anonymous Edit');

        // when
        $this->httpClient->request('GET', '/category/'.$category->getId().'/edit');

        // then
        self::assertResponseRedirects('/category');
    }

    /**
     * Test edit action for an administrator.
     */
    public function testEdit(): void
    {
        // given
        $category = $this->createCategory('Test Controller Category Before Edit');
        $categoryId = $category->getId();
        $newTitle = 'Test Controller Category After Edit';
        $this->httpClient->loginUser($this->createAdmin());
        $crawler = $this->httpClient->request('GET', '/category/'.$categoryId.'/edit');
        $form = $crawler->filter('form')->form([
            'category[title]' => $newTitle,
        ]);

        // when
        $this->httpClient->submit($form);

        // then
        self::assertResponseRedirects('/category');
        $editedCategory = $this->getEntityManager()
            ->getRepository(Category::class)
            ->find($categoryId);
        self::assertInstanceOf(Category::class, $editedCategory);
        self::assertSame($newTitle, $editedCategory->getTitle());
    }

    /**
     * Test edit action with invalid data.
     */
    public function testEditWithInvalidData(): void
    {
        // given
        $category = $this->createCategory('Test Controller Category Invalid Edit');
        $categoryId = $category->getId();
        $this->httpClient->loginUser($this->createAdmin());
        $crawler = $this->httpClient->request('GET', '/category/'.$categoryId.'/edit');
        $form = $crawler->filter('form')->form([
            'category[title]' => '',
        ]);

        // when
        $this->httpClient->submit($form);

        // then
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.alert-danger');
        $entityManager = $this->getEntityManager();
        $entityManager->clear();
        $unchangedCategory = $entityManager
            ->getRepository(Category::class)
            ->find($categoryId);
        self::assertInstanceOf(Category::class, $unchangedCategory);
        self::assertSame('Test Controller Category Invalid Edit', $unchangedCategory->getTitle());
    }

    /**
     * Test delete action for an anonymous user.
     */
    public function testDeleteRedirectsAnonymousUser(): void
    {
        // given
        $category = $this->createCategory('Test Controller Category Anonymous Delete');

        // when
        $this->httpClient->request('GET', '/category/'.$category->getId().'/delete');

        // then
        self::assertResponseRedirects('/category');
    }

    /**
     * Test delete action for an administrator.
     */
    public function testDelete(): void
    {
        // given
        $category = $this->createCategory('Test Controller Category Delete');
        $categoryId = $category->getId();
        $this->httpClient->loginUser($this->createAdmin());
        $crawler = $this->httpClient->request('GET', '/category/'.$categoryId.'/delete');
        $form = $crawler->filter('form')->form();

        // when
        $this->httpClient->submit($form);

        // then
        self::assertResponseRedirects('/category');
        $deletedCategory = $this->getEntityManager()
            ->getRepository(Category::class)
            ->find($categoryId);
        self::assertNull($deletedCategory);
    }

    /**
     * Test deleting a category that contains a newspaper.
     */
    public function testDeleteCategoryWithNewspaper(): void
    {
        // given
        $category = $this->createCategory('Test Controller Category With Newspaper');
        $admin = $this->createAdmin();
        $this->createNewspaper($category, $admin);
        $this->httpClient->loginUser($admin);

        // when
        $this->httpClient->request('GET', '/category/'.$category->getId().'/delete');

        // then
        self::assertResponseRedirects('/category');
        $existingCategory = $this->getEntityManager()
            ->getRepository(Category::class)
            ->find($category->getId());
        self::assertInstanceOf(Category::class, $existingCategory);
    }

    /**
     * Create category.
     *
     * @param string $title Category title
     *
     * @return Category Category
     */
    private function createCategory(string $title): Category
    {
        $category = new Category();
        $category->setTitle($title);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }

    /**
     * Create administrator.
     *
     * @return User Administrator
     */
    private function createAdmin(): User
    {
        $admin = new User();
        $admin->setEmail(uniqid('admin_', true).'@example.com');
        $admin->setPassword('test-password');
        $admin->setRoles(['ROLE_ADMIN']);
        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        return $admin;
    }

    /**
     * Create newspaper.
     *
     * @param Category $category Newspaper category
     * @param User     $author   Newspaper author
     *
     * @return Newspaper Newspaper
     */
    private function createNewspaper(Category $category, User $author): Newspaper
    {
        $newspaper = new Newspaper();
        $newspaper->setTitle('Test Controller Newspaper');
        $newspaper->setContent('Test newspaper content.');
        $newspaper->setCategory($category);
        $newspaper->setAuthor($author);
        $this->entityManager->persist($newspaper);
        $this->entityManager->flush();

        return $newspaper;
    }

    /**
     * Get current entity manager.
     *
     * @return EntityManagerInterface Entity manager
     */
    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
