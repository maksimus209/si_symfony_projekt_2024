<?php
/**
 * Registration Controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class RegistrationController.
 */
class RegistrationController extends AbstractController
{
    private UserPasswordHasherInterface $passwordHasher;
    private UserRepository $userRepository;
    private ValidatorInterface $validator;

    /**
     * RegistrationController constructor.
     *
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     * @param UserRepository              $userRepository User repository
     * @param ValidatorInterface          $validator      Validator
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository, ValidatorInterface $validator)
    {
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
        $this->validator = $validator;
    }

    /**
     * Register action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $plainPassword = $request->request->get('plainPassword');
            $confirmPassword = $request->request->get('confirmPassword');

            // Walidacja danych wejściowych
            $emailConstraint = new Assert\Email();
            $passwordConstraint = new Assert\Length(['min' => 6]);

            $emailViolations = $this->validator->validate($email, $emailConstraint);
            $passwordViolations = $this->validator->validate($plainPassword, $passwordConstraint);

            if (count($emailViolations) > 0 || count($passwordViolations) > 0 || $plainPassword !== $confirmPassword) {
                $this->addFlash('error', 'Invalid input data.');

                return $this->render('registration/register.html.twig');
            }

            $user = new User();
            $user->setEmail($email);
            $user->setRoles(['ROLE_USER']);
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $this->userRepository->save($user, true);

            $this->addFlash('success', 'Registration successful!');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig');
    }
}
