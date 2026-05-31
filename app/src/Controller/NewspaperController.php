<?php
/**
 * Newspaper controller.
 */

namespace App\Controller;

use App\Dto\NewspaperListInputFiltersDto;
use App\Entity\Comment;
use App\Entity\Rating;
use App\Entity\Newspaper;
use App\Entity\User;
use App\Form\Type\CommentType;
use App\Form\Type\RatingType;
use App\Form\Type\NewspaperType;
use App\Repository\RatingRepository;
use App\Resolver\NewspaperListInputFiltersDtoResolver;
use App\Service\NewspaperServiceInterface;
use App\Service\TagServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class NewspaperController.
 */
#[Route('/newspaper')]
class NewspaperController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param NewspaperServiceInterface $newspaperService Newspaper Service Interface
     * @param TagServiceInterface       $tagService       Tag Service Interface
     * @param TranslatorInterface       $translator       Translator
     * @param Security                  $security         Security
     * @param ManagerRegistry           $doctrine         Manager Registry Doctrine
     */
    public function __construct(private readonly NewspaperServiceInterface $newspaperService, private readonly TagServiceInterface $tagService, private readonly TranslatorInterface $translator, private readonly Security $security, private readonly ManagerRegistry $doctrine)
    {
    }

    /**
     * Newspaper index.
     *
     * @param NewspaperListInputFiltersDto $filters Filters
     * @param int                          $page    Page
     *
     * @return Response Index
     */
    #[Route(name: 'newspaper_index', methods: 'GET')]
    public function index(#[MapQueryString(resolver: NewspaperListInputFiltersDtoResolver::class)] NewspaperListInputFiltersDto $filters, #[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->newspaperService->getPaginatedList(
            $page,
            null,
            $filters
        );

        return $this->render('newspaper/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Own newspapers.
     *
     * @param NewspaperListInputFiltersDto $filters Filters
     * @param int                          $page    Page
     *
     * @return Response Own
     */
    #[Route(
        '/own',
        name: 'newspaper_own',
        methods: 'GET'
    )]
    public function own(#[MapQueryString(resolver: NewspaperListInputFiltersDtoResolver::class)] NewspaperListInputFiltersDto $filters, #[MapQueryParameter] int $page = 1): Response
    {
        $user = $this->getUser();
        $pagination = $this->newspaperService->getPaginatedList(
            $page,
            $user,
            $filters
        );

        return $this->render('newspaper/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Newspaper details, actions show.
     *
     * @param Newspaper              $newspaper Entity Newspaper
     * @param Request                $request   Request
     * @param EntityManagerInterface $em        Entity Manager Interface
     *
     * @return Response Show, details
     */
    #[Route(
        '/{id}',
        name: 'newspaper_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|POST'
    )]
    public function show(Newspaper $newspaper, Request $request, EntityManagerInterface $em): Response
    {
        $comments = $newspaper->getComments();
        $user = $this->security->getUser();
        $commentForm = null;
        if ($user instanceof User) {
            $comment = new Comment();
            $comment->setNewspaper($newspaper);
            $comment->setAuthor($user);
            $form = $this->createForm(CommentType::class, $comment);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->newspaperService->saveComment($comment);
                $this->addFlash('success', $this->translator->trans('message.created_comment_successfully'));

                return $this->redirectToRoute('newspaper_show', ['id' => $newspaper->getId()]);
            }
            $commentForm = $form->createView();
        }

        return $this->render('newspaper/show.html.twig', [
            'id' => $newspaper->getId(),
            'newspaper' => $newspaper,
            'comments' => $comments,
            'commentForm' => $commentForm,
        ]);
    }

    /**
     * Deleting a comment, action deleteComment.
     *
     * @param Request                $request   Request
     * @param Newspaper              $newspaper Entity Newspaper
     * @param Comment                $comment   Entity Comment
     * @param EntityManagerInterface $em        Entity Manager Interface
     *
     * @return Response Delete comment
     */
    #[Route(
        '/{newspaper_id}/comment/{id}/delete',
        name: 'comment_delete',
        requirements: ['id' => '[1-9]\d*', 'newspaper_id' => '[1-9]\d*'],
        methods: 'POST'
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteComment(Request $request, Newspaper $newspaper, Comment $comment, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user || ($comment->getAuthor() !== $user && !$this->isGranted('ROLE_ADMIN'))) {
            return $this->redirectToRoute('newspaper_show', ['id' => $newspaper->getId()]);
        }
        $newspaperId = $comment->getNewspaper()->getId();
        $form = $this->createForm(FormType::class, $comment, [
            'method' => 'POST',
            'action' => $this->generateUrl('comment_delete', ['newspaper_id' => $newspaperId, 'id' => $comment->getId()]),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->newspaperService->deleteComment($comment);
            $this->addFlash('success', $this->translator->trans('message.deleted_comment_successfully'));

            return $this->redirectToRoute('newspaper_show', ['id' => $newspaperId]);
        }

        return $this->render('comment/delete.html.twig', [
            'form' => $form->createView(),
            'comment' => $comment,
        ]);
    }

    /**
     * Create a newspaper, action create.
     *
     * @param Request $request Request
     *
     * @return Response Create
     */
    #[Route('/create', name: 'newspaper_create', methods: 'GET|POST')]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        $newspaper = new Newspaper();
        $newspaper->setAuthor($user);
        $form = $this->createForm(
            NewspaperType::class,
            $newspaper,
            ['action' => $this->generateUrl('newspaper_create')]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->newspaperService->save($newspaper);
            $this->addFlash(
                'success',
                $this->translator->trans('message.created_newspaper_successfully')
            );

            return $this->redirectToRoute('newspaper_index');
        }

        return $this->render(
            'newspaper/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Editing a newspaper, action edit.
     *
     * @param Request $request Request
     * @param Newspaper $newspaper Entity Newspaper
     *
     * @return Response Edit
     */
    #[Route('/{id}/edit', name: 'newspaper_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|POST')]
    public function edit(Request $request, Newspaper $newspaper): Response
    {
        $user = $this->getUser();
        if ($newspaper->getAuthor() !== $user && !$this->isGranted('ROLE_ADMIN') || !$user) {
            $this->addFlash(
                'warning',
                $this->translator->trans('message.newspaper_no_permission')
            );

            return $this->redirectToRoute('newspaper_index');
        }
        $form = $this->createForm(
            NewspaperType::class,
            $newspaper,
            [
                'method' => 'POST',
                'action' => $this->generateUrl('newspaper_edit', ['id' => $newspaper->getId()]), ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->newspaperService->save($newspaper);
            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_newspaper_successfully')
            );

            return $this->redirectToRoute('newspaper_show', ['id' => $newspaper->getId()]);
        }

        return $this->render(
            'newspaper/edit.html.twig',
            [
                'form' => $form->createView(),
                'newspaper' => $newspaper,
            ]
        );
    }

    /**
     * Deleting a newspaper, action delete.
     *
     * @param Request $request Request
     * @param Newspaper $newspaper Entity Newspaper
     *
     * @return Response Delete
     */
    #[Route('/{id}/delete', name: 'newspaper_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|POST')]
    public function delete(Request $request, Newspaper $newspaper): Response
    {
        $user = $this->getUser();
        if ($newspaper->getAuthor() !== $user && !$this->isGranted('ROLE_ADMIN') || !$user) {
            $this->addFlash(
                'warning',
                $this->translator->trans('message.newspaper_no_permission')
            );

            return $this->redirectToRoute('newspaper_index');
        }
        $form = $this->createForm(
            FormType::class,
            $newspaper,
            [
                'method' => 'POST',
                'action' => $this->generateUrl('newspaper_delete', ['id' => $newspaper->getId()]),
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->newspaperService->delete($newspaper);
            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_newspaper_successfully')
            );

            return $this->redirectToRoute('newspaper_index');
        }

        return $this->render(
            'newspaper/delete.html.twig',
            [
                'form' => $form->createView(),
                'newspaper' => $newspaper,
            ]
        );
    }

    /**
     * Rating a newspaper, action rateNewspaper.
     *
     * @param Request          $request          Request
     * @param Newspaper        $newspaper        Entity Newspaper
     * @param RatingRepository $ratingRepository Rating Repository
     *
     * @return Response Rating
     */
    #[Route(
        '/{id}/rate',
        name: 'newspaper_rate',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET', 'POST']
    )]
    public function rateNewspaper(Request $request, Newspaper $newspaper, RatingRepository $ratingRepository): Response
    {
        $user = $this->getUser();
        $existingRating = $ratingRepository->findOneBy([
            'newspaper' => $newspaper,
            'user' => $user,
        ]);
        if ($existingRating) {
            $rating = $existingRating;
        } else {
            $rating = new Rating();
            $rating->setNewspaper($newspaper);
            $rating->setUser($user);
        }
        $form = $this->createForm(RatingType::class, $rating);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->doctrine->getManager();
            $this->newspaperService->saveRating($rating);
            $newspaper->calculateAverageRating();
            $entityManager->persist($newspaper);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $this->translator->trans('message.rated_successfully')
            );

            return $this->redirectToRoute('newspaper_show', ['id' => $newspaper->getId()]);
        }

        return $this->render('newspaper/rate.html.twig', [
            'newspaper' => $newspaper,
            'form' => $form->createView(),
        ]);
    }
}
