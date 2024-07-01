<?php

/**
 * Admin repository.
 */

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 *  Class AdminRepository.
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<User>
 *
 * @psalm-suppress LessSpecificImplementedReturnType
 */
class AdminRepository extends ServiceEntityRepository
{
    private PaginatorInterface $paginator;
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * Constructor for the User Repository.
     *
     * @param ManagerRegistry             $registry       The ManagerRegistry service instance managing entities.
     * @param PaginatorInterface          $paginator      The PaginatorInterface service for pagination operations.
     * @param UserPasswordHasherInterface $passwordHasher The UserPasswordHasherInterface service for hashing user passwords.
     */
    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct($registry, User::class);
        $this->paginator = $paginator;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Save user entity.
     *
     * @param User $user User entity
     */
    public function save(User $user): void
    {
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * Update user entity.
     *
     * @param User $user User entity
     */
    public function update(User $user): void
    {
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * Updates the password of a user entity and persists it.
     *
     * @param User   $user          The user entity for which to update the password
     * @param string $plainPassword The plain password to hash and set for the user
     */
    public function updatePassword(User $user, string $plainPassword): void
    {
        $encodedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($encodedPassword);

        $this->update($user);
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
        $queryBuilder = $this->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC');

        return $this->paginator->paginate($queryBuilder, $page, $limit);
    }
}
