<?php

/**
 * Registration service.
 */

namespace App\Service;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Security\LoginFormAuthenticator;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Class RegistrationService
 *
 * Service responsible for user registration.
 */
class RegistrationService implements RegistrationServiceInterface
{
    private EntityManagerInterface $entityManager;
    private FormFactoryInterface $formFactory;
    private UserPasswordHasherInterface $passwordHasher;
    private UserAuthenticatorInterface $userAuthenticator;
    private LoginFormAuthenticator $authenticator;
    private Security $security;
    private UserRepository $userRepository;

    /**
     * RegistrationService constructor.
     *
     * @param EntityManagerInterface      $entityManager
     * @param FormFactoryInterface        $formFactory
     * @param UserPasswordHasherInterface $passwordHasher
     * @param UserAuthenticatorInterface  $userAuthenticator
     * @param LoginFormAuthenticator      $authenticator
     * @param Security                    $security
     * @param UserRepository              $userRepository
     */
    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formFactory, UserPasswordHasherInterface $passwordHasher, UserAuthenticatorInterface $userAuthenticator, LoginFormAuthenticator $authenticator, Security $security, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->passwordHasher = $passwordHasher;
        $this->userAuthenticator = $userAuthenticator;
        $this->authenticator = $authenticator;
        $this->security = $security;
        $this->userRepository = $userRepository;
    }

    /**
     * Registers a new user.
     *
     * @param User    $user    The user entity to register
     * @param Request $request The request object containing form data
     *
     * @return bool Returns true if registration was successful, false otherwise
     */
    public function register(User $user, Request $request): bool
    {
        $form = $this->formFactory->create(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $form->get('password')->getData()));

            $this->userRepository->save($user);


            $this->userAuthenticator->authenticateUser($user, $this->authenticator, $request);

            return true;
        }

        return false;
    }
}
