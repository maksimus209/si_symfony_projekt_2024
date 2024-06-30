<?php
/**
 * User Manager Service Interface.
 */

namespace App\Service;

use App\Entity\User;

/**
 * Interface UserManagerServiceInterface
 *
 */
interface UserManagerServiceInterface
{
    /**
     * Zmienia email użytkownika.
     *
     * @param User   $user  Użytkownik
     * @param string $email Nowy email
     */
    public function changeEmail(User $user, string $email): void;

    /**
     * Zmienia hasło użytkownika.
     *
     * @param User   $user            Użytkownik
     * @param string $currentPassword Obecne hasło
     * @param string $newPassword     Nowe hasło
     *
     * @return bool Zwraca true, jeśli hasło zostało pomyślnie zmienione, w przeciwnym razie false
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool;

    /**
     * Zapisuje zmiany użytkownika w bazie danych.
     *
     * @param User $user Użytkownik
     */
    public function save(User $user): void;
}
