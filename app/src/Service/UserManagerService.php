<?php
/**
 * User Manager Service.
 */

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserManagerService.
 *
 * Serwis odpowiedzialny za operacje na kontach użytkowników.
 */
class UserManagerService implements UserManagerServiceInterface
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * UserManagerService constructor.
     *
     * @param EntityManagerInterface      $entityManager  Menadżer encji
     * @param UserPasswordHasherInterface $passwordHasher Hasher haseł użytkowników
     */
    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Zmienia email użytkownika.
     *
     * @param User   $user  Użytkownik
     * @param string $email Nowy email
     */
    public function changeEmail(User $user, string $email): void
    {
        $user->setEmail($email);
    }

    /**
     * Zmienia hasło użytkownika.
     *
     * @param User   $user            Użytkownik
     * @param string $currentPassword Obecne hasło
     * @param string $newPassword     Nowe hasło
     *
     * @return bool Zwraca true, jeśli hasło zostało pomyślnie zmienione, w przeciwnym razie false
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
            return false;
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));

        return true;
    }

    /**
     * Zapisuje zmiany użytkownika w bazie danych.
     *
     * @param User $user Użytkownik
     */
    public function save(User $user): void
    {
        $this->entityManager->flush();
    }

    /**
     * Zwraca listę wszystkich użytkowników.
     *
     * @return User[] Lista użytkowników
     */
    public function findAllUsers(): array
    {
        return $this->entityManager->getRepository(User::class)->findAll();
    }
}
