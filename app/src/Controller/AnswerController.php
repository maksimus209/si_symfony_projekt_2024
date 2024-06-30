<?php
/**
 * Answer controller.
 */

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Form\Type\AnswerType;
use App\Service\AnswerServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class AnswerController.
 */
#[Route('/answer')]
class AnswerController extends AbstractController
{
    private AnswerServiceInterface $answerService;
    private TranslatorInterface $translator;

    /**
     * Constructor.
     *
     * @param AnswerServiceInterface $answerService Serwis odpowiedzi
     * @param TranslatorInterface    $translator    Tłumacz
     */
    public function __construct(AnswerServiceInterface $answerService, TranslatorInterface $translator)
    {
        $this->answerService = $answerService;
        $this->translator = $translator;
    }

    /**
     * Create a new answer.
     *
     * @param Request  $request  HTTP request
     * @param Question $question Question entity
     *
     * @return Response HTTP response
     */
    #[Route('/new/{question}', name: 'answer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Question $question): Response
    {
        $answer = new Answer();
        $answer->setQuestion($question);

        if ($this->getUser()) {
            $answer->setAuthor($this->getUser());
        }

        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        // filtrowanie
        if ($form->isSubmitted() && $form->isValid()) {
            $this->answerService->createAnswer($answer, $question);

            return $this->redirectToRoute('question_show', ['id' => $question->getId()]);
        }

        return $this->render('answer/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Mark answer as best.
     *
     * @param Answer $answer Answer entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/best', name: 'answer_best', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function markAsBest(Answer $answer): Response
    {
        $this->answerService->markAsBest($answer);

        return $this->redirectToRoute('question_show', ['id' => $answer->getQuestion()->getId()]);
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Answer  $answer  Answer entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'answer_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Answer $answer): Response
    {
        if ($this->isCsrfTokenValid('delete'.$answer->getId(), $request->request->get('_token'))) {
            $this->answerService->deleteAnswer($answer);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );
        }

        return $this->redirectToRoute('question_show', ['id' => $answer->getQuestion()->getId()]);
    }
}
