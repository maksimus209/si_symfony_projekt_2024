<?php
/**
 * UserController.
 */

namespace App\Controller;

use App\Form\Type\ChangeEmailAndPasswordType;
use App\Service\UserAccountServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * Class UserController.
 */
class UserController extends AbstractController
{
    private UserAccountServiceInterface $userAccountService;
    private TranslatorInterface $translator;

    /**
     * UserController constructor.
     *
     * @param UserAccountServiceInterface $userAccountService Serwis zarządzania kontami użytkowników
     * @param TranslatorInterface         $translator         Tłumacz
     */
    public function __construct(UserAccountServiceInterface $userAccountService, TranslatorInterface $translator)
    {
        $this->userAccountService = $userAccountService;
        $this->translator = $translator;
    }

    /**
     * Wyświetla profil użytkownika.
     *
     * @return Response HTTP response
     */
    #[Route('/profile', name: 'user_profile', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function profile(): Response
    {
        $user = $this->getUser();

        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Akcja zmiany emaila i hasła.
     *
     * @param Request                     $request        HTTP request
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     *
     * @return Response HTTP response
     */
    #[Route('/profile/change-email-password', name: 'user_change_email_password', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function changeEmailAndPassword(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var PasswordAuthenticatedUserInterface|null $user */
        $user = $this->getUser();
        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw new \LogicException('User is not authenticated.');
        }

        $form = $this->createForm(ChangeEmailAndPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Zmiana emaila
            $email = $form->get('email')->getData();
            $this->userAccountService->changeEmail($user, $email);

            // Zmiana hasła
            $currentPassword = $form->get('currentPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();
            if (!empty($newPassword) && !$this->userAccountService->changePassword($user, $currentPassword, $newPassword)) {
                $this->addFlash('error', $this->translator->trans('message.invalid_current_password'));

                return $this->redirectToRoute('user_change_email_password');
            }

            $this->userAccountService->save($user);

            $this->addFlash('success', $this->translator->trans('message.email_password_changed_successfully'));

            return $this->redirectToRoute('user_profile');
        }

        return $this->render('user/change_email_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
