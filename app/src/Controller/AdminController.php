<?php
/*
 * Admin Controller
 */

namespace App\Controller;

use App\Form\Type\ChangeEmailAndPasswordType;
use App\Service\AccountServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * Class AdminController.
 */
class AdminController extends AbstractController
{
    private AccountServiceInterface $accountService;

    /**
     * AdminController constructor.
     *
     * @param AccountServiceInterface $accountService Serwis konta
     */
    public function __construct(AccountServiceInterface $accountService)
    {
        $this->accountService = $accountService;
    }

    /**
     * Akcja profilu administratora.
     *
     * @return Response HTTP response
     */
    #[Route('/admin/profile', name: 'admin_profile', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function profile(): Response
    {
        $user = $this->getUser();

        return $this->render('admin/profile.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Akcja zmiany emaila i hasła.
     *
     * @param Request             $request    HTTP request
     * @param TranslatorInterface $translator Translator
     *
     * @return Response HTTP response
     */
    #[Route('/admin/change-email-password', name: 'admin_change_email_password', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function changeEmailAndPassword(Request $request, TranslatorInterface $translator): Response
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
            $this->accountService->changeEmail($user, $email);

            // Zmiana hasła
            $currentPassword = $form->get('currentPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();
            if (!empty($newPassword) && !$this->accountService->changePassword($user, $currentPassword, $newPassword)) {
                $this->addFlash('error', $translator->trans('message.invalid_current_password'));

                return $this->redirectToRoute('admin_change_email_password');
            }

            $this->accountService->save($user);

            $this->addFlash('success', $translator->trans('message.email_password_changed_successfully'));

            return $this->redirectToRoute('admin_profile');
        }

        return $this->render('admin/change_email_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
