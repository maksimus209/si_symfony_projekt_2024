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
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class RegistrationController.
 */
class RegistrationController extends AbstractController
{
    private UserServiceInterface $userService;
    private TranslatorInterface $translator;

    /**
     * RegistrationController constructor.
     *
     * @param UserServiceInterface $userService User service
     * @param TranslatorInterface  $translator  Translator
     */
    public function __construct(UserServiceInterface $userService, TranslatorInterface $translator)
    {
        $this->userService = $userService;
        $this->translator = $translator;
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
                $this->addFlash('error', $this->translator->trans('message.invalid_input_data'));

                return $this->render('registration/register.html.twig');
            }

            $this->addFlash('success', $this->translator->trans('message.registration_successful'));

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig');
    }
}
