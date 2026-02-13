<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class EventType extends AbstractType
{
    // src/Form/EventType.php
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('dateStart', null, [
                'widget' => 'single_text', // <--- C'EST CA QUI CHANGE TOUT
            ])
            ->add('dateEnd', null, [
                'widget' => 'single_text', // <--- C'EST CA QUI CHANGE TOUT
            ])
            ->add('maxCapacity')
            ->add('isOnline')
            ->add('location')
            // NE PAS AJOUTER organizer_id NI meet_link ICI
        ;
        $builder->add('image', FileType::class, [
            'label' => 'Image de l\'événement',
            'mapped' => false, // Important : on gère l'upload manuellement
            'required' => false,
            'attr' => ['class' => 'form-input-custom'],
            'constraints' => [
                new File([
                    'maxSize' => '2M',
                    'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                    'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG, PNG, WEBP)',
                ])
            ],
        ]);
                
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
