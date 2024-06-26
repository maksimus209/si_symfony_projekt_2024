<?php
/*
 * Changing Type
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ChangeEmailAndPasswordType.
 *
 * Form type for changing email and password.
 */
class ChangeEmailAndPasswordType extends AbstractType
{
    private TranslatorInterface $translator;

    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator The translator service
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Build form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The form options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => $this->translator->trans('change_email_password.new_email'),
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
            ])
            ->add('currentPassword', PasswordType::class, [
                'label' => $this->translator->trans('change_email_password.current_password'),
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('newPassword', PasswordType::class, [
                'label' => $this->translator->trans('change_email_password.new_password'),
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\Length(['min' => 6]),
                ],
            ])
            ->add('confirmNewPassword', PasswordType::class, [
                'label' => $this->translator->trans('change_email_password.confirm_new_password'),
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
     */
    public function validatePasswords($value, ExecutionContextInterface $context): void
    {
        $form = $context->getRoot();
        $newPassword = $form->get('newPassword')->getData();

        if ($value !== $newPassword) {
            $context->buildViolation($this->translator->trans('change_email_password.passwords_do_not_match'))
                ->atPath('confirmNewPassword')
                ->addViolation();
        }
    }

    /**
     * Configure options.
     *
     * @param OptionsResolver $resolver The options resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
