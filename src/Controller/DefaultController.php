<?php

/**
 * Default controller.
 */

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController.
 */
class DefaultController extends AbstractController
{
    /**
     * Renders the homepage.
     *
     * @param CategoryRepository $categoryRepository The category repository
     *
     * @return Response HTTP response
     *
     * */
    #[Route('/', name: 'homepage')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render('base.html.twig', [
            'categories' => $categories,
        ]);
    }
}
