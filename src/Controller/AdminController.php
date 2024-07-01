<?php

/**
 * Admin controller.
 */

namespace App\Controller;

use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use App\Entity\User;
use App\Form\Type\UserEditType;
use App\Service\AdminServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
* Class AdminController
 *
*/
class AdminController extends AbstractController
{
    private AdminServiceInterface $adminService;
    private TranslatorInterface $translator;


    /**
     * AdminController constructor.
     *
     * @param AdminServiceInterface $adminService The admin service used for business logic
     * @param TranslatorInterface   $translator   The translator
     */
    public function __construct(AdminServiceInterface $adminService, TranslatorInterface $translator)
    {
        $this->adminService = $adminService;
        $this->translator = $translator;
    }

    /**
    * Display the list of users for administrators.
     *
     *  @param int $page The page number for pagination
    *
    * @return Response The response object
    *
    * */
    #[Route('/admin/users', name: 'admin_user_list')]
    public function userList(#[MapQueryParameter] int $page = 1): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $limit = 10;
        $pagination = $this->adminService->getPaginatedUsers($page, $limit);

        return $this->render('admin/user_list.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * Edit user details by administrators.
     *
     * @param Request                     $request        The HTTP request object
     * @param User                        $user           The user entity to edit
     * @param UserPasswordHasherInterface $passwordHasher The password hasher service
     *
     * @return Response The response object
     *
     */
    #[Route('/admin/users/edit/{id}', name: 'admin_user_edit')]
    public function editUser(Request $request, User $user, UserPasswordHasherInterface $passwordHasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(UserEditType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            if ($plainPassword) {
                $this->adminService->updateUserPassword($user, $plainPassword);
            } else {
                $this->adminService->updateUser($user);
            }

            $this->addFlash('success', $this->translator->trans('message.edited_successfully'));

            return $this->redirectToRoute('admin_user_list');
        }

        return $this->render('admin/edit.html.twig', [
            'userForm' => $form->createView(),
            'user' => $user,
        ]);
    }
}
