<?php

/**
 * Album controller.
 */

namespace App\Controller;

use App\Service\AlbumService;
use App\Form\Type\AlbumType;
use App\Entity\Album;
use App\Entity\User;
use App\Entity\Comment;
use App\Repository\TagRepository;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CommentRepository;
use App\Repository\AlbumRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use App\Repository\CategoryRepository;

/**
 * Class AlbumController.
 */
#[Route('/')]
class AlbumController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager The entity manager
     * @param AlbumService           $albumService  The album service
     * @param TranslatorInterface    $translator    The translator
     */
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly AlbumService $albumService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Index action.
     *
     * @param AlbumRepository    $albumRepository Album repository
     * @param PaginatorInterface $paginator       Paginator
     * @param Request            $request         HTTP request
     *
     * @return Response HTTP response
     *
     */
    #[Route('/', name: 'album_index', methods: ['GET'])]
    public function index(AlbumRepository $albumRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $categoryId = $request->query->getInt('category', 0);

        $pagination = $this->albumService->getPaginatedList($page, $categoryId);

        return $this->render('album/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * Remove favorite action.
     *
     * @param int      $id       Album ID
     * @param Security $security Security component
     *
     * @return Response HTTP response
     *
     * */
    #[Route('/album/{id}/remove-favorite', name: 'remove_favorite')]
    public function removeFavorite(int $id, Security $security): Response
    {
        $user = $security->getUser();


        try {
            $this->albumService->removeFavorite($id, $user);
            $this->addFlash('success', $this->translator->trans('message.removedFav'));
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $this->translator->trans('message.doesnotexist'));
        }

        return $this->redirectToRoute('app_account', ['id' => $id]);
    }


    /**
     * Albums by tag action.
     *
     * @param int             $id              Tag ID
     * @param AlbumRepository $albumRepository Album repository
     * @param TagRepository   $tagRepository   Tag repository
     *
     * @return Response HTTP response
     * */
    #[Route('/albums/tag/{id}', name:'album_by_tag', methods:['GET'])]
    public function albumsByTag(int $id, AlbumRepository $albumRepository, TagRepository $tagRepository): Response
    {
        $tag = $tagRepository->find($id);

        if (!$tag) {
            throw $this->createNotFoundException('The tag does not exist');
        }

        $albums = $this->albumService->getAlbumsByTag($tag);

        return $this->render('album/by_tag.html.twig', [
            'tag' => $tag,
            'albums' => $albums,
        ]);
    }


    /**
     * Favorite action.
     *
     * @param int                    $id       Album ID
     * @param EntityManagerInterface $em       Entity manager
     * @param Security               $security Security component
     *
     * @return Response               HTTP response
     *
     * */
    #[Route('/album/{id}/favorite', name: 'favorite_album', methods: ['POST'])]
    public function favorite(int $id, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();
        $album = $em->getRepository(Album::class)->find($id);

        if (!$album) {
            throw $this->createNotFoundException('The album does not exist');
        }
        $this->albumService->toggleFavorite($album, $user);

        $message = $user->getFavorites()->contains($album) ? 'message.addedFav' : 'message.removedFav';
        $this->addFlash('success', $this->translator->trans($message));

        return $this->redirectToRoute('album_show', ['id' => $album->getId()]);
    }


    /**
     * Show action.
     *
     * @param Album             $album             Album entity
     * @param CommentRepository $commentRepository Comment repository
     *
     * @return Response HTTP response
     *
     */
    #[Route(
        '/{id}',
        name: 'album_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET',
    )]
    #[IsGranted('VIEW', subject: 'album')]
    public function show(Album $album, CommentRepository $commentRepository): Response
    {
        $commentForm = $this->createForm(CommentType::class, new Comment());

        return $this->render('album/show.html.twig', [
            'album' => $album,
            'comment_form' => $commentForm->createView(),
            'comments' => $commentRepository->findBy(['album' => $album]),
        ]);
    }


    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     *
     **/
    #[Route('/create', name: 'album_create', methods: 'GET|POST')]
    #[IsGranted('CREATE', subject: 'album')]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('No access for you!');
        }
        $user = $this->getUser();
        $album = new Album();
        $album->setAuthor($user);
        $form = $this->createForm(AlbumType::class, $album, ['action' => $this->generateUrl('album_create')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->albumService->save($album);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('album_index');
        }

        return $this->render('album/create.html.twig', ['form' => $form->createView()]);
    }


    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Album   $album   Album entity
     *
     * @return Response HTTP response
     *
     */
    #[Route('/{id}/edit', name: 'album_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Album $album): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('No access for you!');
        }
        $form = $this->createForm(AlbumType::class, $album, ['method' => 'PUT', 'action' => $this->generateUrl('album_edit', ['id' => $album->getId()]), ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->albumService->save($album);

            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_successfully')
            );

            return $this->redirectToRoute('album_index');
        }



        return $this->render(
            'album/edit.html.twig',
            [
            'form' => $form->createView(),
            'album' => $album,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Album   $album   Album entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'album_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Album $album): Response
    {

        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('No access for you!');
        }
        $form = $this->createForm(
            FormType::class,
            $album,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('album_delete', ['id' => $album->getId()]),
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->albumService->delete($album);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('album_index');
        }

        return $this->render(
            'album/delete.html.twig',
            [
                'form' => $form->createView(),
                'album' => $album,
            ]
        );
    }
}
