<?php

namespace App\Form;

use App\Entity\Behandeling;
use App\Repository\BehandelingRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class BehandelingFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', IntegerType::class)
            ->add('groep', ChoiceType::class, [
                'placeholder' => 'Selecteer een groep',
                'choices' => [
                    'Nagels' => [
                        'Nieuwe set' => 'NN',
                        'Nabehandeling' => 'NA',
                        'Handen' => 'NH',
                        'Voeten' => 'NV',
                    ],
                    'Haar' => [
                        'Dames' => 'HD',
                        'Heren' => 'HH',
                        'Kinderen t/m 11 jaar' => 'HK',
                        'Kinderen 12 t/m 15 jaar' => 'HT',
                    ],
                ]
            ])
            ->add('naam', TextType::class, [])
            ->add('prijs', MoneyType::class, [
                'grouping' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Behandeling::class,
        ]);
    }
}
