<?php
/*
 * User Service
 */

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserService.
 */
class UserService implements UserServiceInterface
{
    private UserPasswordHasherInterface $passwordHasher;
    private UserRepository $userRepository;
    private ValidatorInterface $validator;

    /**
     * UserService constructor.
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository, ValidatorInterface $validator)
    {
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
        $this->validator = $validator;
    }

    public function register(string $email, string $password, string $confirmPassword): ?User
    {
        // Validate input
        $emailConstraint = new Assert\Email();
        $passwordConstraint = new Assert\Length(['min' => 6]);

        $emailViolations = $this->validator->validate($email, $emailConstraint);
        $passwordViolations = $this->validator->validate($password, $passwordConstraint);

        if (count($emailViolations) > 0 || count($passwordViolations) > 0 || $password !== $confirmPassword) {
            return null;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_USER']);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->userRepository->save($user, true);

        return $user;
    }
}
