<?php
/**
 * Answer controller.
 */

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Form\Type\AnswerType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\AnswerRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class AnswerController.
 */
#[Route('/answer')]
class AnswerController extends AbstractController
{
    private AnswerRepository $answerRepository;
    private TranslatorInterface $translator;

    /**
     * Constructor.
     *
     * @param AnswerRepository    $answerRepository Answer repository
     * @param TranslatorInterface $translator       Translator
     */
    public function __construct(AnswerRepository $answerRepository, TranslatorInterface $translator)
    {
        $this->answerRepository = $answerRepository;
        $this->translator = $translator;
    }

    /**
     * Create a new answer.
     *
     * @param Request                $request       HTTP request
     * @param Question               $question      Question entity
     * @param EntityManagerInterface $entityManager Entity manager
     *
     * @return Response HTTP response
     */
    #[Route('/new/{question}', name: 'answer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Question $question, EntityManagerInterface $entityManager): Response
    {
        $answer = new Answer();
        $answer->setQuestion($question);

        if ($this->getUser()) {
            $answer->setAuthor($this->getUser());
        }

        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        //filtrowanie
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($answer);
            $entityManager->flush();

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
        $answer->setIsBest(true);
        $this->answerRepository->save($answer, true);

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
            $this->answerRepository->remove($answer, true);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );
        }

        return $this->redirectToRoute('question_show', ['id' => $answer->getQuestion()->getId()]);
    }
}
