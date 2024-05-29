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
use App\Service\QuestionServiceInterface;
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
/**
 * Class QuestionController.
 */
#[Route('/question')]
class QuestionController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param QuestionServiceInterface $questionService Question service
     * @param TranslatorInterface      $translator      Translator
     */
    public function __construct(private readonly QuestionServiceInterface $questionService, private readonly TranslatorInterface $translator)
    {
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
        $pagination = $this->questionService->getPaginatedList($page);

        return $this->render('question/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Show action.
     *
     * @param Question         $question         Question entity
     * @param Request          $request          HTTP request
     * @param AnswerRepository $answerRepository Answer repository
     *
     * @return Response HTTP response
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

        return $this->render('question/show.html.twig', [
            'question' => $question,
            'answerForm' => $form->createView(),
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
    #[IsGranted('ROLE_ADMIN')]
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
            $this->questionService->save($question);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('question_index');
        }

        return $this->render('question/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Edit action.
     *
     * @param Request  $request  HTTP request
     * @param Question $question Question entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'question_edit', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Question $question): Response
    {
        $form = $this->createForm(
            QuestionType::class,
            $question,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('question_edit', ['id' => $question->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->questionService->save($question);

            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_successfully')
            );

            return $this->redirectToRoute('question_index');
        }

        return $this->render(
            'question/edit.html.twig',
            [
                'form' => $form->createView(),
                'question' => $question,
            ]
        );
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

        return $this->render(
            'question/delete.html.twig',
            [
                'form' => $form->createView(),
                'question' => $question,
            ]
        );
    }
}
