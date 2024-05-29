<?php
/**
 * Category controller.
 */

namespace App\Controller;

use App\Entity\Category;
use App\Form\Type\CategoryType;
use App\Service\CategoryServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Repository\QuestionRepository;

/**
 * Class CategoryController.
 */
#[Route('/category')]
class CategoryController extends AbstractController
{
    private CategoryServiceInterface $categoryService;
    private TranslatorInterface $translator;

    /**
     * Constructor.
     *
     * @param CategoryServiceInterface $categoryService Category service
     * @param TranslatorInterface      $translator      Translator
     */
    public function __construct(CategoryServiceInterface $categoryService, TranslatorInterface $translator)
    {
        $this->categoryService = $categoryService;
        $this->translator = $translator;
    }

    /**
     * Index action.
     *
     * @param int $page Page number
     *
     * @return Response HTTP response
     */
    #[Route(name: 'category_index', methods: 'GET')]
    public function index(int $page = 1): Response
    {
        $pagination = $this->categoryService->getPaginatedList($page);

        return $this->render('category/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Show action.
     *
     * @param Category $category Category
     *
     * @return Response HTTP response
     */
    #[Route('/{id}', name: 'category_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Category $category): Response
    {
        $questions = $category->getQuestions();

        return $this->render('category/show.html.twig', [
            'category' => $category,
            'questions' => $questions,
        ]);
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/create', name: 'category_create', methods: 'GET|POST')]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->categoryService->save($category);

            $this->addFlash('success', $this->translator->trans('message.created_successfully'));

            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edit action.
     *
     * @param Request  $request  HTTP request
     * @param Category $category Category
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'category_edit', requirements: ['id' => '[1-9]\\d*'], methods: 'GET|PUT')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryType::class, $category, [
            'method' => 'PUT',
            'action' => $this->generateUrl('category_edit', ['id' => $category->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->categoryService->save($category);

            $this->addFlash('success', $this->translator->trans('message.updated_successfully'));

            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/edit.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
        ]);
    }

    /**
     * Delete action.
     *
     * @param Request  $request  HTTP request
     * @param Category $category Category
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'category_delete', requirements: ['id' => '[1-9]\\d*'], methods: 'GET|DELETE')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Category $category): Response
    {
        if (!$this->categoryService->canBeDeleted($category)) {
            $this->addFlash('warning', $this->translator->trans('message.category_contains_questions'));

            return $this->redirectToRoute('category_index');
        }

        $form = $this->createForm(FormType::class, $category, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('category_delete', ['id' => $category->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->categoryService->delete($category);

            $this->addFlash('success', $this->translator->trans('message.deleted_successfully'));

            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/delete.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
        ]);
    }

    /**
     * Show questions for a category.
     *
     * @param Category           $category           Category
     * @param QuestionRepository $questionRepository Question repository
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/questions', name: 'category_questions', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function showQuestions(Category $category, QuestionRepository $questionRepository): Response
    {
        $questions = $questionRepository->findBy(['category' => $category]);

        return $this->render('category/questions.html.twig', [
            'category' => $category,
            'questions' => $questions,
        ]);
    }
}
