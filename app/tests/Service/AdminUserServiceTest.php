<?php

/**
 * Administrator user service tests.
 */

namespace App\Tests\Service;

use App\Entity\Comment;
use App\Entity\Newspaper;
use App\Entity\Rating;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\NewspaperRepository;
use App\Repository\RatingRepository;
use App\Repository\UserRepository;
use App\Service\AdminUserService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class AdminUserServiceTest.
 */
class AdminUserServiceTest extends TestCase
{
    /**
     * Test get all users.
     */
    public function testGetAllUsers(): void
    {
        // given
        $users = [new User(), new User()];
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findAll')->willReturn($users);
        $service = $this->createService(userRepository: $userRepository);

        // when
        $result = $service->getAllUsers();

        // then
        self::assertSame($users, $result);
    }

    /**
     * Test update user.
     */
    public function testUpdateUser(): void
    {
        // given
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');
        $service = $this->createService(entityManager: $entityManager);

        // when
        $service->updateUser(new User());

        // then
        self::addToAssertionCount(1);
    }

    /**
     * Test change password.
     */
    public function testChangePassword(): void
    {
        // given
        $user = new User();
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')->with($user, 'new')->willReturn('hashed');
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');
        $service = $this->createService(
            passwordHasher: $passwordHasher,
            entityManager: $entityManager
        );

        // when
        $service->changePassword($user, 'new');

        // then
        self::assertSame('hashed', $user->getPassword());
    }

    /**
     * Test delete user.
     *
     * @return void Void
     */
    public function testDeleteUser(): void
    {
        // given
        $user = new User();
        $otherUser = new User();
        $comment = $this->createMock(Comment::class);
        $remainingRating = $this->createMock(Rating::class);
        $remainingRating->method('getUser')->willReturn($otherUser);
        $remainingRating->method('getValue')->willReturn(4);

        $newspaperWithRating = $this->createMock(Newspaper::class);
        $newspaperWithRating->method('getAuthor')->willReturn($otherUser);
        $newspaperWithoutRating = $this->createMock(Newspaper::class);
        $newspaperWithoutRating->method('getAuthor')->willReturn($otherUser);
        $authoredNewspaper = $this->createMock(Newspaper::class);
        $authoredNewspaper->method('getAuthor')->willReturn($user);

        $removedRating = $this->createRating($user, $newspaperWithRating);
        $onlyRemovedRating = $this->createRating($user, $newspaperWithoutRating);
        $authoredRating = $this->createRating($user, $authoredNewspaper);
        $newspaperWithRating->method('getRatings')->willReturn(
            new ArrayCollection([$removedRating, $remainingRating])
        );
        $newspaperWithoutRating->method('getRatings')->willReturn(
            new ArrayCollection([$onlyRemovedRating])
        );
        $newspaperWithRating->expects(self::once())->method('setAverageRating')->with(4.0);
        $newspaperWithoutRating->expects(self::once())->method('setAverageRating')->with(0);

        $commentRepository = $this->createMock(CommentRepository::class);
        $commentRepository->method('findBy')->with(['author' => $user])->willReturn([$comment]);
        $ratingRepository = $this->createMock(RatingRepository::class);
        $ratingRepository->method('findBy')->with(['user' => $user])->willReturn([
            $removedRating,
            $onlyRemovedRating,
            $authoredRating,
        ]);
        $newspaperRepository = new class([$authoredNewspaper]) extends NewspaperRepository
        {
            /**
             * @param Newspaper[] $newspapers Newspapers
             */
            public function __construct(private readonly array $newspapers)
            {
            }

            /**
             * Find newspapers by author.
             *
             * @param User $user User
             *
             * @return Newspaper[] Newspapers
             */
            public function findByAuthor(User $user): array
            {
                return $this->newspapers;
            }
        };
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::exactly(6))->method('remove');
        $entityManager->expects(self::once())->method('flush');
        $service = $this->createService(
            newspaperRepository: $newspaperRepository,
            commentRepository: $commentRepository,
            ratingRepository: $ratingRepository,
            entityManager: $entityManager
        );

        // when
        $service->deleteUser($user);

        // then
        self::addToAssertionCount(1);
    }

    /**
     * Create rating.
     *
     * @param User      $user      User
     * @param Newspaper $newspaper Newspaper
     *
     * @return Rating Rating
     */
    private function createRating(User $user, Newspaper $newspaper): Rating
    {
        $rating = $this->createMock(Rating::class);
        $rating->method('getUser')->willReturn($user);
        $rating->method('getNewspaper')->willReturn($newspaper);

        return $rating;
    }

    /**
     * Create service.
     *
     * @param UserRepository|null              $userRepository      User repository
     * @param NewspaperRepository|null         $newspaperRepository Newspaper repository
     * @param CommentRepository|null           $commentRepository   Comment repository
     * @param RatingRepository|null            $ratingRepository    Rating repository
     * @param UserPasswordHasherInterface|null $passwordHasher      Password hasher
     * @param EntityManagerInterface|null      $entityManager       Entity manager
     *
     * @return AdminUserService Service
     */
    private function createService(?UserRepository $userRepository = null, ?NewspaperRepository $newspaperRepository = null, ?CommentRepository $commentRepository = null, ?RatingRepository $ratingRepository = null, ?UserPasswordHasherInterface $passwordHasher = null, ?EntityManagerInterface $entityManager = null): AdminUserService
    {
        return new AdminUserService(
            $userRepository ?? $this->createMock(UserRepository::class),
            $newspaperRepository ?? $this->createMock(NewspaperRepository::class),
            $commentRepository ?? $this->createMock(CommentRepository::class),
            $ratingRepository ?? $this->createMock(RatingRepository::class),
            $passwordHasher ?? $this->createMock(UserPasswordHasherInterface::class),
            $entityManager ?? $this->createMock(EntityManagerInterface::class),
        );
    }
}
