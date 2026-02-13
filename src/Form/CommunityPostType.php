<?php

namespace App\Form;

use App\Entity\CommunityPost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommunityPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Share a tip, ask a question, or start a discussion...',
                    'rows' => 3,
                    'class' => 'form-input',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'choices' => [
                    '💡 Tip' => 'tip',
                    '❓ Question' => 'question',
                    '💬 Discussion' => 'discussion',
                    '🎉 Celebration' => 'celebration',
                ],
                'attr' => ['class' => 'form-input feed-type-select'],
            ])
            ->add('tag', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => '#tag (optional)',
                    'class' => 'form-input',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommunityPost::class,
        ]);
    }
}
