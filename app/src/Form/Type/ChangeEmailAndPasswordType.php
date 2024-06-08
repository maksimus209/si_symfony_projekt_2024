<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class ChangeEmailAndPasswordType
 *
 * Form type for changing email and password.
 */
class ChangeEmailAndPasswordType extends AbstractType
{
    /**
     * Build form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The form options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'New Email',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
            ])
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Current Password',
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('newPassword', PasswordType::class, [
                'label' => 'New Password',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\Length(['min' => 6]),
                ],
            ])
            ->add('confirmNewPassword', PasswordType::class, [
                'label' => 'Confirm New Password',
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Callback(['callback' => [$this, 'validatePasswords']]),
                ],
            ]);
    }

    /**
     * Validate passwords.
     *
     * @param mixed                     $value   The value
     * @param ExecutionContextInterface $context The execution context
     *
     * @return void
     */
    public function validatePasswords($value, ExecutionContextInterface $context): void
    {
        $form = $context->getRoot();
        $newPassword = $form->get('newPassword')->getData();

        if ($value !== $newPassword) {
            $context->buildViolation('Passwords do not match.')
                ->atPath('confirmNewPassword')
                ->addViolation();
        }
    }

    /**
     * Configure options.
     *
     * @param OptionsResolver $resolver The options resolver
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
