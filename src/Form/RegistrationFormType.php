<?php

namespace App\Form;

use App\Entity\Klant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'email*',
            ])
            //->add('agreeTerms', CheckboxType::class, [
            //    'mapped' => false,
            //    'constraints' => [
            //        new IsTrue([
            //            'message' => 'You should agree to our terms.',
            //        ]),
            //    ],
            //])
            ->add('voornaam', TextType::class, [
                'label' => 'voornaam*',
            ])
            ->add('achternaam', TextType::class, [
                'label' => 'achternaam*',
            ])
            ->add('straat', TextType::class, [
                'label' => 'straat',
                'required' => false,
            ])
            ->add('postcode', TextType::class, [
                'label' => 'postcode',
                'required' => false,
            ])
            ->add('woonplaats', TextType::class, [
                'label' => 'woonplaats',
                'required' => false,
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'label' => 'wachtwoord*',
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('passwordCheck', PasswordType::class, [
                'mapped' => false,
                'label' => 'wachtwoord herhalen*',
                'constraints' => [
                    new NotBlank([
                        'message' => 'You need to this to complete the registration',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Klant::class,
        ]);
    }
}
