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

/**
 * Class AnswerController.
 */
#[Route('/answer')]
class AnswerController extends AbstractController
{
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

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($answer);
            $entityManager->flush();

            return $this->redirectToRoute('question_show', ['id' => $question->getId()]);
        }

        return $this->render('answer/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
