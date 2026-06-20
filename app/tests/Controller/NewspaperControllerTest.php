<?php

/**
 * Newspaper controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Newspaper;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class NewspaperControllerTest.
 */
class NewspaperControllerTest extends WebTestCase
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
     * Test show action for an anonymous user.
     */
    public function testShow(): void
    {
        // given
        $category = new Category();
        $category->setTitle('Test Newspaper Category');

        $author = new User();
        $author->setEmail('newspaper-author@example.com');
        $author->setPassword('test-password');
        $author->setRoles(['ROLE_USER']);

        $newspaper = new Newspaper();
        $newspaper->setTitle('Test Newspaper Controller');
        $newspaper->setContent('Test newspaper controller content.');
        $newspaper->setCategory($category);
        $newspaper->setAuthor($author);

        $this->entityManager->persist($category);
        $this->entityManager->persist($author);
        $this->entityManager->persist($newspaper);
        $this->entityManager->flush();

        // when
        $this->httpClient->request('GET', '/newspaper/'.$newspaper->getId());

        // then
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', (string) $newspaper->getTitle());
        self::assertSelectorTextContains('body', (string) $newspaper->getContent());
        self::assertSelectorTextContains('body', (string) $category->getTitle());
    }
}
