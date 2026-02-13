<?php

namespace App\Form;

use App\Entity\Reminder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class ReminderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Reminder Title',
                'attr' => [
                    'placeholder' => 'Reminder title...',
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Add details...',
                    'class' => 'form-control',
                    'rows' => 3
                ]
            ])
            ->add('reminderTime', DateTimeType::class, [
                'label' => 'Reminder Date & Time',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'datetime-local'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reminder::class,
        ]);
    }
}
