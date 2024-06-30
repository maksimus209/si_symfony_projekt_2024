<?php
/**
 * Answer Service.
 */

namespace App\Service;

use App\Entity\Answer;
use App\Entity\Question;
use App\Repository\AnswerRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class AnswerService.
 *
 * Serwis odpowiedzialny za operacje na odpowiedziach.
 */
class AnswerService implements AnswerServiceInterface
{
    private EntityManagerInterface $entityManager;
    private AnswerRepository $answerRepository;

    /**
     * AnswerService constructor.
     *
     * @param EntityManagerInterface $entityManager    Menadżer encji
     * @param AnswerRepository       $answerRepository Repozytorium odpowiedzi
     */
    public function __construct(EntityManagerInterface $entityManager, AnswerRepository $answerRepository)
    {
        $this->entityManager = $entityManager;
        $this->answerRepository = $answerRepository;
    }

    /**
     * Tworzy nową odpowiedź.
     *
     * @param Answer   $answer   Odpowiedź
     * @param Question $question Pytanie
     */
    public function createAnswer(Answer $answer, Question $question): void
    {
        $answer->setQuestion($question);
        $this->entityManager->persist($answer);
        $this->entityManager->flush();
    }

    /**
     * Oznacza odpowiedź jako najlepszą.
     *
     * @param Answer $answer Odpowiedź
     */
    public function markAsBest(Answer $answer): void
    {
        $answer->setIsBest(true);
        $this->answerRepository->save($answer, true);
    }

    /**
     * Usuwa odpowiedź.
     *
     * @param Answer $answer Odpowiedź
     */
    public function deleteAnswer(Answer $answer): void
    {
        $this->answerRepository->remove($answer, true);
    }

    /**
     * Zwraca listę wszystkich odpowiedzi.
     *
     * @return Answer[] Lista odpowiedzi
     */
    public function findAllAnswers(): array
    {
        return $this->answerRepository->findAll();
    }
}
