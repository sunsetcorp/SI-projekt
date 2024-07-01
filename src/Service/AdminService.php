<?php

/**
 * Admin service.
 */

namespace App\Service;

use App\Entity\User;
use App\Repository\AdminRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Service for administrative operations on users.
 */
class AdminService implements AdminServiceInterface
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private PaginatorInterface $paginator;
    private AdminRepository $adminRepository;


    /**
     * AdminService constructor.
     *
     * @param EntityManagerInterface      $entityManager   The entity manager for database operations
     * @param UserPasswordHasherInterface $passwordHasher  The password hasher for hashing user passwords
     * @param PaginatorInterface          $paginator       The paginator service
     * @param AdminRepository             $adminRepository The admin repository
     */
    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, PaginatorInterface $paginator, AdminRepository $adminRepository)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->paginator = $paginator;
        $this->adminRepository = $adminRepository;
    }

    /**
     * Retrieves all users from the database.
     *
     * @return User[] The array of User objects representing all users
     */
    public function getAllUsers(): array
    {
        return $this->entityManager->getRepository(User::class)->findAll();
    }

    /**
     * Retrieves paginated users from the database.
     *
     * @param int $page  The current page number
     * @param int $limit The number of users per page
     *
     * @return PaginationInterface The paginator object containing the users
     */
    public function getPaginatedUsers(int $page = 1, int $limit = 10): PaginationInterface
    {
        return $this->adminRepository->getPaginatedUsers($page, $limit);
    }

    /**
     * Updates the user entity in the database.
     *
     * @param User $user The user entity to update
     */
    public function updateUser(User $user): void
    {
        $this->adminRepository->update($user);
    }

    /**
     * Updates the password of a user entity and persists it.
     *
     * @param User   $user          The user entity for which to update the password
     * @param string $plainPassword The plain password to hash and set for the user
     */
    public function updateUserPassword(User $user, string $plainPassword): void
    {
        $this->adminRepository->updatePassword($user, $plainPassword);
    }
}
