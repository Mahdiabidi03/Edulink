<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', null, [
                'attr' => ['class' => 'form-input', 'placeholder' => 'Full Name'],
                'label' => false,
                'required' => false,
            ])
            ->add('email', null, [
                'attr' => ['class' => 'form-input', 'placeholder' => 'Email Address'],
                'label' => false,
                'required' => false,
            ])
            ->add('plainPassword', \Symfony\Component\Form\Extension\Core\Type\RepeatedType::class, [
                'type' => \Symfony\Component\Form\Extension\Core\Type\PasswordType::class,
                'mapped' => false,
                'required' => false,
                'first_options'  => ['label' => false, 'attr' => ['class' => 'form-input', 'placeholder' => 'Password'], 'required' => false],
                'second_options' => ['label' => false, 'attr' => ['class' => 'form-input', 'placeholder' => 'Confirm Password'], 'required' => false],
                'invalid_message' => 'The password fields must match.',
            ])
            ->add('agreeTerms', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\IsTrue([
                        'message' => 'You must agree to our terms.',
                    ]),
                ],
                'label' => 'I agree to the terms and conditions',
                'attr' => ['class' => 'form-checkbox'],
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
