<?php

/**
 * Comment service.
 */

namespace App\Service;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class CommentService.
 *
 * Service class for managing comments.
 */
class CommentService implements CommentServiceInterface
{
    private CommentRepository $commentRepository;
    private EntityManagerInterface $entityManager;

    /**
     * CommentService constructor.
     *
     * @param CommentRepository      $commentRepository The comment repository
     * @param EntityManagerInterface $entityManager     The entity manager
     */
    public function __construct(CommentRepository $commentRepository, EntityManagerInterface $entityManager)
    {
        $this->commentRepository = $commentRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Save a comment entity.
     *
     * @param Comment $comment The comment entity to save
     */
    public function save(Comment $comment): void
    {
        $this->commentRepository->save($comment, true);
    }

    /**
     * Remove a comment entity.
     *
     * @param Comment $comment The comment entity to remove
     */
    public function remove(Comment $comment): void
    {
        $this->commentRepository->remove($comment, true);
    }
}
