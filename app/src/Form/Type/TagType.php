<?php
/**
 * TagType form.
 */

namespace App\Form\Type;

use App\Entity\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TagType.
 */
class TagType extends AbstractType
{
    /**
     * Build form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array<string, mixed> $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'label' => 'Tag Name',
            'attr' => [
                'class' => 'form-control',
            ],
        ]);

        $builder->add('save', SubmitType::class, [
            'label' => 'Save Tag',
            'attr' => [
                'class' => 'btn btn-primary mt-3',
            ],
        ]);
    }

    /**
     * Configure options.
     *
     * @param OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tag::class,
        ]);
    }
}
