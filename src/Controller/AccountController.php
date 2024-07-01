<?php

/**
 * Account controller.
 */

namespace App\Controller;

use App\Service\AccountServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AccountController
*
 */
class AccountController extends AbstractController
{
    private AccountServiceInterface $accountService;
    private TranslatorInterface $translator;


    /**
     * AccountController constructor.
     *
     * @param AccountServiceInterface $accountService The account service used for business logic
     * @param TranslatorInterface     $translator     The translator
     */
    public function __construct(AccountServiceInterface $accountService, TranslatorInterface $translator)
    {
        $this->accountService = $accountService;
        $this->translator = $translator;
    }

    /**
     * Render the account page.
     *
     * @return Response The response object
     *
     * @Route("/account", name="app_account")
     */
    #[Route('/account', name: 'app_account')]
    public function account(): Response
    {
        return $this->accountService->renderAccountPage();
    }

    /**
     * Handle account edit request.
     *
     * @param Request $request The HTTP request object
     *
     * @return Response The response object
     *
     */
    #[Route('/account/edit', name: 'app_account_edit')]
    public function edit(Request $request): Response
    {
        $response = $this->accountService->handleAccountEdit($request);


        if ($request->isMethod('POST') && $response instanceof RedirectResponse) {
            $this->addFlash('success', $this->translator->trans('message.edited_successfully'));
        }

        return $response;
    }
}
