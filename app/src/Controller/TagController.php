<?php
/**
 * Tag Controller.
 */

namespace App\Controller;

use App\Entity\Tag;
use App\Form\Type\TagType;
use App\Service\TagServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class TagController.
 */
#[Route('/tag')]
class TagController extends AbstractController
{
    private TagServiceInterface $tagService;

    /**
     * Constructor.
     *
     * @param TagServiceInterface $tagService Tag service
     */
    public function __construct(TagServiceInterface $tagService)
    {
        $this->tagService = $tagService;
    }

    /**
     * Index action.
     *
     * @return Response HTTP response
     */
    #[Route('/', name: 'tag_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('tag/index.html.twig', [
            'tags' => $this->tagService->getAllTags(),
        ]);
    }

    /**
     * New action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/new', name: 'tag_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request): Response
    {
        $tag = $this->tagService->createTag($request);

        if (null !== $tag) {
            return $this->redirectToRoute('tag_index');
        }

        return $this->render('tag/new.html.twig', [
            'form' => $this->createForm(TagType::class, $tag)->createView(),
        ]);
    }

    /**
     * Show action.
     *
     * @param Tag $tag Tag entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}', name: 'tag_show', methods: ['GET'])]
    public function show(Tag $tag): Response
    {
        return $this->render('tag/show.html.twig', [
            'tag' => $tag,
        ]);
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Tag     $tag     Tag entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'tag_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Tag $tag): Response
    {
        $updatedTag = $this->tagService->updateTag($request, $tag);

        if (null !== $updatedTag) {
            return $this->redirectToRoute('tag_index');
        }

        return $this->render('tag/edit.html.twig', [
            'form' => $this->createForm(TagType::class, $tag)->createView(),
        ]);
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Tag     $tag     Tag entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}', name: 'tag_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Tag $tag): Response
    {
        $this->tagService->deleteTag($request, $tag);

        return $this->redirectToRoute('tag_index');
    }
}
