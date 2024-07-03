<?php

/**
 * Comment controller.
 */

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Comment;
use App\Form\CommentDeleteFormType;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Service\CommentServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class CommentController.
 */
class CommentController extends AbstractController
{
    private CommentServiceInterface $commentService;
    private CommentRepository $commentRepository;
    private TranslatorInterface $translator;

    /**
     * Constructor.
     *
     * @param CommentServiceInterface $commentService    The comment service
     * @param CommentRepository       $commentRepository The comment repository
     * @param TranslatorInterface     $translator        The translator
     */
    public function __construct(CommentServiceInterface $commentService, CommentRepository $commentRepository, TranslatorInterface $translator)
    {
        $this->commentService = $commentService;
        $this->commentRepository = $commentRepository;
        $this->translator = $translator;
    }

    /**
     * Add comment action.
     *
     * @param Request $request HTTP request
     * @param Album   $album   Album entity
     *
     * @return Response HTTP response
     **/
    #[Route('/album/{id}/comment', name: 'comment_add', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function add(Request $request, Album $album): Response
    {
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);

        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setAuthor($this->getUser());
            $comment->setAlbum($album);
            $comment->setCreatedAt(new \DateTimeImmutable());

            $this->commentService->save($comment);

            return $this->redirectToRoute('album_show', ['id' => $album->getId()]);
        }

        return $this->render('album/show.html.twig', [
            'album' => $album,
            'comment_form' => $commentForm->createView(),
            'comments' => $this->commentRepository->findBy(['album' => $album]),
        ]);
    }

    /**
     * Delete comment action.
     *
     * @param Request $request HTTP request
     * @param Comment $comment Comment entity
     *
     * @return Response HTTP response
     */
    #[Route('/comment/{id}/delete', name: 'comment_delete', requirements: ['id' => '\d+'], methods: ['GET', 'DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Comment $comment): Response
    {
        $form = $this->createForm(CommentDeleteFormType::class, null, [
            'action' => $this->generateUrl('comment_delete', ['id' => $comment->getId()]),
            'method' => 'DELETE',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commentService->remove($comment);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('album_show', ['id' => $comment->getAlbum()->getId()]);
        }

        return $this->render('comment/delete.html.twig', [
            'form' => $form->createView(),
            'comment' => $comment,
        ]);
    }
}
