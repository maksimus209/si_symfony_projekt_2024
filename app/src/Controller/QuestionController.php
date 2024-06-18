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
 * Klasa QuestionController.
 */
#[Route('/question')]
class QuestionController extends AbstractController
{
    private AnswerRepository $answerRepository;
    private TagRepository $tagRepository;
    private CategoryRepository $categoryRepository;
    private EntityManagerInterface $entityManager;

    /**
     * Konstruktor.
     *
     * @param QuestionServiceInterface $questionService    Serwis pytań
     * @param AnswerRepository         $answerRepository   Repozytorium odpowiedzi
     * @param TagRepository            $tagRepository      Repozytorium tagów
     * @param CategoryRepository       $categoryRepository Repozytorium kategorii
     * @param EntityManagerInterface   $entityManager      Menedżer encji
     * @param TranslatorInterface      $translator         Tłumacz
     */
    public function __construct(QuestionServiceInterface $questionService, AnswerRepository $answerRepository, TagRepository $tagRepository, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->questionService = $questionService;
        $this->answerRepository = $answerRepository;
        $this->tagRepository = $tagRepository;
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * Akcja indeksowania.
     *
     * @param int $page Numer strony
     *
     * @return Response Odpowiedź HTTP
     */
    #[Route(name: 'question_index', methods: ['GET'])]
    public function index(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->questionService->getPaginatedList($page);
        $tags = $this->tagRepository->findAll();
        $categories = $this->categoryRepository->findAll(); // Pobierz wszystkie kategorie

        return $this->render('question/index.html.twig', [
            'pagination' => $pagination,
            'tags' => $tags,
            'categories' => $categories, // Przekaż kategorie do widoku
        ]);
    }

    /**
     * Akcja wyświetlania.
     *
     * @param Question         $question         Encja pytania
     * @param Request          $request          Żądanie HTTP
     * @param AnswerRepository $answerRepository Repozytorium odpowiedzi
     *
     * @return Response Odpowiedź HTTP
     */
    #[Route('/{id}', name: 'question_show', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'POST'])]
    public function show(Question $question, Request $request, AnswerRepository $answerRepository): Response
    {
        $answer = new Answer();
        $answer->setQuestion($question);

        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $answer->setAuthor($this->getUser());
            $answerRepository->save($answer, true);

            return $this->redirectToRoute('question_show', ['id' => $question->getId()]);
        }

        // Pobierz odpowiedzi powiązane z pytaniem
        $answers = $answerRepository->findBy(['question' => $question]);

        // Sortowanie odpowiedzi, aby najlepsza była na górze
        usort($answers, function ($a, $b) {
            if ($a->getIsBest() === $b->getIsBest()) {
                return 0;
            }

            return $a->getIsBest() ? -1 : 1;
        });

        $tags = $this->tagRepository->findAll(); // Pobierz wszystkie tagi

        return $this->render('question/show.html.twig', [
            'question' => $question,
            'answerForm' => $form->createView(),
            'answers' => $answers,
            'tags' => $tags, // Przekaż tagi do widoku
        ]);
    }

    /**
     * Akcja tworzenia.
     *
     * @param Request $request Żądanie HTTP
     *
     * @return Response Odpowiedź HTTP
     */
    #[Route('/create', name: 'question_create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): Response
    {
        $question = new Question();
        $form = $this->createForm(
            QuestionType::class,
            $question,
            ['action' => $this->generateUrl('question_create')]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $question->setAuthor($this->getUser());
            $this->questionService->save($question);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('question_index');
        }

        $tags = $this->tagRepository->findAll(); // Pobierz wszystkie tagi

        return $this->render('question/create.html.twig', [
            'form' => $form->createView(),
            'tags' => $tags, // Przekaż tagi do widoku
        ]);
    }

    /**
     * Akcja edycji.
     *
     * @param Request  $request  Żądanie HTTP
     * @param Question $question Encja pytania
     *
     * @return Response Odpowiedź HTTP
     */
    #[Route('/{id}/edit', name: 'question_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Question $question): Response
    {
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.updated_successfully')
            );

            return $this->redirectToRoute('question_index');
        }

        return $this->render('question/edit.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Akcja usuwania.
     *
     * @param Request  $request  Żądanie HTTP
     * @param Question $question Encja pytania
     *
     * @return Response Odpowiedź HTTP
     */
    #[Route('/{id}/delete', name: 'question_delete', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Question $question): Response
    {
        $form = $this->createForm(
            FormType::class,
            $question,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('question_delete', ['id' => $question->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->questionService->delete($question);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('question_index');
        }

        $tags = $this->tagRepository->findAll(); // Pobierz wszystkie tagi

        return $this->render('question/delete.html.twig', [
            'form' => $form->createView(),
            'question' => $question,
            'tags' => $tags, // Przekaż tagi do widoku
        ]);
    }

    /**
     * Oznacz odpowiedź jako najlepszą.
     *
     * @param Answer $answer Encja odpowiedzi
     *
     * @return Response Odpowiedź HTTP
     */
    #[Route('/answer/{id}/best', name: 'answer_best', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function markAsBest(Answer $answer): Response
    {
        $question = $answer->getQuestion();
        $user = $this->getUser();

        // Sprawdź, czy użytkownik jest autorem pytania lub adminem
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
