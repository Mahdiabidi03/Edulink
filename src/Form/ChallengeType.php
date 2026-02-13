<?php

namespace App\Form;

use App\Entity\Challenge;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ChallengeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => ['novalidate' => 'novalidate']
            ])
            ->add('goal', TextareaType::class, [
                'label' => 'Objectif',
                'attr' => ['novalidate' => 'novalidate']
            ])
            ->add('rewardPoints', IntegerType::class, [
                'label' => 'Points de récompense',
                'attr' => ['novalidate' => 'novalidate']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Challenge::class,
        ]);
    }
}
