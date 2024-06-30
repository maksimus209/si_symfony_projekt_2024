<?php
/*
 * This file is part of the Q&A application.
 *
 * (c) Your Company <info@yourcompany.com>
 */

/**
 * Admin User Controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\ChangeEmailAndPasswordType;
use App\Service\UserManagerServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class AdminUserController.
 */
#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminUserController extends AbstractController
{
    private UserManagerServiceInterface $userManagerService;

    /**
     * AdminUserController constructor.
     *
     * @param UserManagerServiceInterface $userManagerService Serwis zarządzania użytkownikami
     */
    public function __construct(UserManagerServiceInterface $userManagerService)
    {
        $this->userManagerService = $userManagerService;
    }

    /**
     * Lists all users.
     *
     * @return Response HTTP response
     */
    #[Route('/users', name: 'admin_user_index', methods: ['GET'])]
    public function index(): Response
    {
        $users = $this->userManagerService->findAllUsers();

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * Edit user.
     *
     * @param Request             $request    HTTP request
     * @param User                $user       User entity
     * @param TranslatorInterface $translator Translator
     *
     * @return Response HTTP response
     */
    #[Route('/user/{id}/edit', name: 'admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ChangeEmailAndPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Zmiana emaila
            $email = $form->get('email')->getData();
            $this->userManagerService->changeEmail($user, $email);

            // Zmiana hasła
            $currentPassword = $form->get('currentPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();
            if (!empty($newPassword) && !$this->userManagerService->changePassword($user, $currentPassword, $newPassword)) {
                $this->addFlash('error', $translator->trans('message.invalid_current_password'));

                return $this->redirectToRoute('admin_user_edit', ['id' => $user->getId()]);
            }

            $this->userManagerService->save($user);

            $this->addFlash('success', $translator->trans('message.user_updated_successfully'));

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
