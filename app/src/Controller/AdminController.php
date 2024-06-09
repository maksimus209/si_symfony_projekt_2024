<?php
/*
 * Admin Controller
 */

namespace App\Controller;

use App\Form\Type\ChangeEmailAndPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * Class AdminController.
 */
class AdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    /**
     * AdminController constructor.
     *
     * @param EntityManagerInterface $entityManager The entity manager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Admin profile action.
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
     * Change email and password action.
     *
     * @param Request                     $request        HTTP request
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     * @param TranslatorInterface         $translator     Translator
     *
     * @return Response HTTP response
     */
    #[Route('/admin/change-email-password', name: 'admin_change_email_password', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function changeEmailAndPassword(Request $request, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator): Response
    {
        /** @var PasswordAuthenticatedUserInterface|null $user */
        $user = $this->getUser();
        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw new \LogicException('User is not authenticated.');
        }

        $form = $this->createForm(ChangeEmailAndPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle email change
            $email = $form->get('email')->getData();
            $user->setEmail($email);

            // Handle password change
            $currentPassword = $form->get('currentPassword')->getData();
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', $translator->trans('message.invalid_current_password'));

                return $this->redirectToRoute('admin_change_email_password');
            }

            $newPassword = $form->get('newPassword')->getData();
            if (!empty($newPassword)) {
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            }

            $this->entityManager->flush();

            $this->addFlash('success', $translator->trans('message.email_password_changed_successfully'));

            return $this->redirectToRoute('admin_profile');
        }

        return $this->render('admin/change_email_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
