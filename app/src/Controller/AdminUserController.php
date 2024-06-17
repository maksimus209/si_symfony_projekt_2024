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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class AdminUserController.
 */
#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminUserController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * AdminUserController constructor.
     *
     * @param EntityManagerInterface      $entityManager  Entity manager
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     */
    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Lists all users.
     *
     * @return Response HTTP response
     */
    #[Route('/users', name: 'admin_user_index', methods: ['GET'])]
    public function index(): Response
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();

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
            // Kodowanie nowego hasła przed zapisaniem
            if ($newPassword = $form->get('newPassword')->getData()) {
                $encodedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($encodedPassword);
            }

            $this->entityManager->flush();

            $this->addFlash('success', $translator->trans('message.user_updated_successfully'));

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
