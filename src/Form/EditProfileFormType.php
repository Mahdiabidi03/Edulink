<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', null, [
                'label' => 'Full Name',
                'attr' => ['class' => 'form-input'],
            ])
            ->add('email', null, [
                'label' => 'Email Address',
                'attr' => ['class' => 'form-input'],
            ])
            ->add('plainPassword', \Symfony\Component\Form\Extension\Core\Type\RepeatedType::class, [
                'type' => \Symfony\Component\Form\Extension\Core\Type\PasswordType::class,
                'mapped' => false,
                'required' => false,
                'first_options'  => ['label' => 'New Password (leave blank to keep current)', 'attr' => ['class' => 'form-input', 'autocomplete' => 'new-password'], 'required' => false],
                'second_options' => ['label' => 'Confirm New Password', 'attr' => ['class' => 'form-input', 'autocomplete' => 'new-password'], 'required' => false],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
                'invalid_message' => 'The password fields must match.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
