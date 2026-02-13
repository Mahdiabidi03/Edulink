<?php

namespace App\Form;

use App\Entity\HelpRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class HelpRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class , [
            'label' => 'Title',
            'required' => false,
            'attr' => [
                'placeholder' => 'Enter a brief title for your request',
                'class' => 'form-input'
            ],
            'constraints' => [
                new NotBlank(['message' => 'Please enter a title']),
                new Length(['max' => 255]),
            ],
        ])
            ->add('description', TextareaType::class , [
            'label' => 'Description',
            'required' => false,
            'attr' => [
                'placeholder' => 'Describe your problem in detail...',
                'rows' => 5,
                'class' => 'form-input'
            ],
            'constraints' => [
                new NotBlank(['message' => 'Please provide a description']),
            ],
        ])
            ->add('bounty', IntegerType::class , [
            'label' => 'Bounty (Points)',
            'required' => false,
            'attr' => [
                'placeholder' => '0',
                'class' => 'form-input'
            ],
            'constraints' => [
                new GreaterThan(['value' => -1, 'message' => 'Bounty cannot be negative']),
            ],
        ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HelpRequest::class ,
        ]);
    }
}
