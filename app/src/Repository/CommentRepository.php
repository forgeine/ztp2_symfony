<?php

/**
 * CommentRepository.
 */

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class CommentRepository.
 */
class CommentRepository extends ServiceEntityRepository
{
    /**
     * Construct.
     *
     * @param ManagerRegistry $registry Manager Registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * Save entity.
     *
     * @param Comment $comment Entity Comment
     *
     * @return void Void
     */
    public function save(Comment $comment): void
    {
        $this->getEntityManager()->persist($comment);
        $this->getEntityManager()->flush();
    }

    /**
     * Delete entity.
     *
     * @param Comment $comment Entity Comment
     *
     * @return void Void
     */
    public function delete(Comment $comment): void
    {
        $this->getEntityManager()->remove($comment);
        $this->getEntityManager()->flush();
    }
}
