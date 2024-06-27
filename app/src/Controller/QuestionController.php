<?php
/**
 * Question controller.
 */

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Answer;
use App\Form\Type\QuestionType;
use App\Form\Type\AnswerType;
use App\Repository\AnswerRepository;
use App\Repository\CategoryRepository;
use App\Repository\TagRepository;
use App\Repository\QuestionRepository;
use App\Service\QuestionServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class QuestionController.
 */
#[Route('/question')]
class QuestionController extends AbstractController
{
    private AnswerRepository $answerRepository;
    private TagRepository $tagRepository;
    private CategoryRepository $categoryRepository;
    private QuestionRepository $questionRepository;
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;
    private QuestionServiceInterface $questionService;

    /**
     * Constructor.
     *
     * @param QuestionServiceInterface $questionService    Question service
     * @param AnswerRepository         $answerRepository   Answer repository
     * @param TagRepository            $tagRepository      Tag repository
     * @param CategoryRepository       $categoryRepository Category repository
     * @param QuestionRepository       $questionRepository Question repository
     * @param EntityManagerInterface   $entityManager      Entity manager
     * @param TranslatorInterface      $translator         Translator
     */
    public function __construct(
        QuestionServiceInterface $questionService,
        AnswerRepository $answerRepository,
        TagRepository $tagRepository,
        CategoryRepository $categoryRepository,
        QuestionRepository $questionRepository,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ) {
        $this->questionService = $questionService;
        $this->answerRepository = $answerRepository;
        $this->tagRepository = $tagRepository;
        $this->categoryRepository = $categoryRepository;
        $this->questionRepository = $questionRepository;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * Index action.
     *
     * @param int $page Page number
     *
     * @return Response HTTP response
     */
    #[Route(name: 'question_index', methods: ['GET'])]
    public function index(#[MapQueryParameter] int $page = 1): Response
    {
        // Use the new query method with join
        $queryBuilder = $this->questionRepository->queryAllWithRelations();
        $pagination = $this->questionService->getPaginatedList($page, $queryBuilder);

        return $this->render('question/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * Show action.
     *
     * @param Question $question Question entity
     * @param Request  $request  HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/{id}', name: 'question_show', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'POST'])]
    public function show(Question $question, Request $request): Response
    {
        $answer = new Answer();
        $answer->setQuestion($question);

        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $answer->setAuthor($this->getUser());
            $this->answerRepository->save($answer, true);

            return $this->redirectToRoute('question_show', ['id' => $question->getId()]);
        }

        // Fetch related answers and tags only once
        $answers = $this->answerRepository->findBy(['question' => $question]);

        // Sort answers, putting the best answer on top
        usort($answers, function ($a, $b) {
            return $b->getIsBest() <=> $a->getIsBest();
        });

        return $this->render('question/show.html.twig', [
            'question' => $question,
            'answerForm' => $form->createView(),
            'answers' => $answers,
        ]);
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/create', name: 'question_create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): Response
    {
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question, [
            'action' => $this->generateUrl('question_create')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $question->setAuthor($this->getUser());
            $this->questionService->save($question);

            $this->addFlash('success', $this->translator->trans('message.created_successfully'));

            return $this->redirectToRoute('question_index');
        }

        return $this->render('question/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edit action.
     *
     * @param Request  $request  HTTP request
     * @param Question $question Question entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'question_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Question $question): Response
    {
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('message.updated_successfully'));

            return $this->redirectToRoute('question_index');
        }

        return $this->render('question/edit.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Delete action.
     *
     * @param Request  $request  HTTP request
     * @param Question $question Question entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'question_delete', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Question $question): Response
    {
        $form = $this->createForm(FormType::class, $question, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('question_delete', ['id' => $question->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->questionService->delete($question);

            $this->addFlash('success', $this->translator->trans('message.deleted_successfully'));

            return $this->redirectToRoute('question_index');
        }

        return $this->render('question/delete.html.twig', [
            'form' => $form->createView(),
            'question' => $question,
        ]);
    }

    /**
     * Mark answer as best.
     *
     * @param Answer $answer Answer entity
     *
     * @return Response HTTP response
     */
    #[Route('/answer/{id}/best', name: 'answer_best', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function markAsBest(Answer $answer): Response
    {
        $question = $answer->getQuestion();
        $user = $this->getUser();

        if ($user === $question->getAuthor() || $this->isGranted('ROLE_ADMIN')) {
            $answer->setIsBest(true);
            $this->answerRepository->save($answer, true);

            $this->addFlash('success', 'Odpowiedź została oznaczona jako najlepsza.');
        } else {
            $this->addFlash('error', 'Nie masz uprawnień do oznaczenia tej odpowiedzi jako najlepszej.');
        }

        return $this->redirectToRoute('question_show', ['id' => $question->getId()]);
    }
}
