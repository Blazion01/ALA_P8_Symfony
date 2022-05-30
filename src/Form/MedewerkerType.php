<?php

namespace App\Form;

use App\Entity\Medewerker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Security\Core\Security;

class MedewerkerType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('voornaam', TextType::class, [
                'label' => 'voornaam*',
            ])
            ->add('achternaam', TextType::class, [
                'label' => 'achternaam*',
            ])
            ->add('functie', ChoiceType::class, [
                'choices' => [
                    'Nagelstylist' => 'Nagelstylist',
                    'Kapper' => 'Kapper'
                ],
                'placeholder' => 'Choose an option',
                'label' => 'functie*'
            ])
            ->add('telefoonnummer', TelType::class, [
                'label' => 'telefoonnummer*',
            ])
            ->add('email', EmailType::class, [
                'label' => 'email*',
            ])
            ->add('password', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'label' => 'wachtwoord*',
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

        if($this->security->isGranted('ROLE_OWNER')) {
            $builder
                ->add('roles', ChoiceType::class, [
                    'mapped' => false,
                    'choices' => [
                        'Ja' => "ROLE_ADMIN",
                        'Nee' => false,
                    ],
                    'expanded' => true,
                    'label' => 'Heeft admin privileges'
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Medewerker::class,
        ]);
    }
}
