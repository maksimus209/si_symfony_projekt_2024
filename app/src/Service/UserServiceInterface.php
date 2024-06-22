<?php

namespace App\Service;

use App\Entity\User;

/**
 * Interface UserServiceInterface
 * @package App\Service
 */
interface UserServiceInterface
{
    /**
     * Registers a new user in the system.
     *
     * @param string $email Email address
     * @param string $password Password
     * @param string $confirmPassword Confirmation password
     * @return User|null The registered user or null if registration fails
     */
    public function register(string $email, string $password, string $confirmPassword): ?User;
}
