<?php

/**
 * User fixtures tests.
 */

namespace App\Tests\DataFixtures;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserFixturesTest.
 */
class UserFixturesTest extends KernelTestCase
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
        $fixtures = new UserFixtures(
            $container->get(UserPasswordHasherInterface::class)
        );
        $fixtures->setReferenceRepository(new ReferenceRepository($entityManager));

        // when
        $fixtures->load($entityManager);

        // then
        $users = $entityManager->getRepository(User::class)->findAll();
        self::assertCount(13, $users);
    }
}
