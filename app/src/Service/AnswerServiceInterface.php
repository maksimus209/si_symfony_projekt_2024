<?php
/**
 * Answer Service Interface.
 */

namespace App\Service;

use App\Entity\Answer;
use App\Entity\Question;

/**
 * Interface AnswerServiceInterface.
 *
 * Interfejs serwisu odpowiedzialnego za operacje na odpowiedziach.
 */
interface AnswerServiceInterface
{
    /**
     * Tworzy nową odpowiedź.
     *
     * @param Answer   $answer   Odpowiedź
     * @param Question $question Pytanie
     */
    public function createAnswer(Answer $answer, Question $question): void;

    /**
     * Oznacza odpowiedź jako najlepszą.
     *
     * @param Answer $answer Odpowiedź
     */
    public function markAsBest(Answer $answer): void;

    /**
     * Usuwa odpowiedź.
     *
     * @param Answer $answer Odpowiedź
     */
    public function deleteAnswer(Answer $answer): void;
}
