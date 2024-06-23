<?php
/**
 * Registration Controller.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserServiceInterface;

/**
 * Class RegistrationController.
 */
class RegistrationController extends AbstractController
{
    private UserServiceInterface $userService;

    /**
     * RegistrationController constructor.
     *
     * @param UserServiceInterface $userService User service
     */
    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
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

            $user = $this->userService->register($email, $plainPassword, $confirmPassword);

            if (!$user) {
                $this->addFlash('error', 'Invalid input data.');

                return $this->render('registration/register.html.twig');
            }

            $this->addFlash('success', 'Registration successful!');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig');
    }
}
