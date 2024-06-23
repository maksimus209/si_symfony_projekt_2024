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
     *
     * @param UserPasswordHasherInterface $passwordHasher  Password hasher
     * @param UserRepository               $userRepository  User repository
     * @param ValidatorInterface           $validator       Validator
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository, ValidatorInterface $validator)
    {
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
        $this->validator = $validator;
    }

    /**
     * Register a new user.
     *
     * @param string $email           User email
     * @param string $password        User password
     * @param string $confirmPassword Confirmation of the password
     *
     * @return User|null
     */
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

