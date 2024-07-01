<?php

/**
 * Account service.
 */

namespace App\Service;

/**
 * Service for handling user account operations.
 */

use App\Entity\User;
use App\Form\Type\AccountType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment;

/**
 * Class Account Service.
 */
class AccountService implements AccountServiceInterface
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private Security $security;
    private FormFactoryInterface $formFactory;
    private Environment $twig;
    private UserRepository $userRepository;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface      $entityManager  The entity manager interface
     * @param UserPasswordHasherInterface $passwordHasher The password hasher interface
     * @param Security                    $security       The security component for accessing the current user
     * @param FormFactoryInterface        $formFactory    The form factory interface
     * @param Environment                 $twig           The Twig environment for rendering templates
     * @param User                        $userRepository User Repository
     */
    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, Security $security, FormFactoryInterface $formFactory, Environment $twig, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->security = $security;
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }


    /**
     * Retrieves the current authenticated user.
     *
     * @return mixed|null The current user object or null if no user is authenticated
     */
    public function getCurrentUser(): ?User
    {
        return $this->security->getUser();
    }


    /**
     * Renders the account page for the current authenticated user.
     *
     * This method retrieves the current authenticated user and renders the
     * account page using a Twig template.
     *
     * @return Response The response object containing the rendered HTML content
     *
     * @throws RuntimeException If the Twig rendering fails
     */
    public function renderAccountPage(): Response
    {
        $user = $this->getCurrentUser();

        return new Response($this->twig->render('account/account.html.twig', ['user' => $user, ]));
    }

    /**
     * Handles the account editing process.
     *
     * @param Request $request The current request object
     *
     * @return Response The response object rendering the account edit or error page
     *
     * @throws \RuntimeException If an error occurs during form handling or entity persistence
     */
    public function handleAccountEdit(Request $request): Response
    {
        $user = $this->getCurrentUser();

        if (!$user) {
            return new Response($this->twig->render('error/accessdenied.html.twig'));
        }

        $form = $this->formFactory->create(AccountType::class, $user, [
            'is_admin' => in_array('ROLE_ADMIN', $user->getRoles()),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            if ($plainPassword) {
                $encodedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($encodedPassword);
            }


            $this->userRepository->save($user);



            return new Response($this->twig->render('account/account.html.twig', [
                'user' => $user,
            ]));
        }

        return new Response($this->twig->render('account/edit.html.twig', [
            'accountForm' => $form->createView(),
        ]));
    }
}
