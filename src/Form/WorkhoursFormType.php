<?php

namespace App\Form;

use App\Entity\Werkuren;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class WorkhoursFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('hours', ChoiceType::class, [
                'mapped' => true,
                'expanded' => true,
                'multiple' => true,
                'choices' => [
                    'maandag' => [
                        'MA 10:00-12:00' => 'MA[10-12]',
                        'MA 12:00-14:00' => 'MA[12-14]',
                        'MA 14:00-16:00' => 'MA[14-16]',
                        'MA 16:00-18:00' => 'MA[16-18]',
                    ],
                    'dinsdag' => [
                        'DI 10:00-12:00' => 'DI[10-12]',
                        'DI 12:00-14:00' => 'DI[12-14]',
                        'DI 14:00-16:00' => 'DI[14-16]',
                        'DI 16:00-18:00' => 'DI[16-18]',
                    ],
                    'woensdag' => [
                        'WO 10:00-12:00' => 'WO[10-12]',
                        'WO 12:00-14:00' => 'WO[12-14]',
                        'WO 14:00-16:00' => 'WO[14-16]',
                        'WO 16:00-18:00' => 'WO[16-18]',
                    ],
                    'donderdag' => [
                        'DO 10:00-12:00' => 'DO[10-12]',
                        'DO 12:00-14:00' => 'DO[12-14]',
                        'DO 14:00-16:00' => 'DO[14-16]',
                        'DO 16:00-18:00' => 'DO[16-18]',
                    ],
                    'vrijdag' => [
                        'VR 10:00-12:00' => 'VR[10-12]',
                        'VR 12:00-14:00' => 'VR[12-14]',
                        'VR 14:00-16:00' => 'VR[14-16]',
                        'VR 16:00-18:00' => 'VR[16-18]',
                        'VR 19:00-21:00' => 'VR[19-21]',
                    ],
                    'zaterdag' => [
                        'ZA 10:00-12:30' => 'ZA[9-12:30]',
                        'ZA 12:30-15:00' => 'ZA[12:30-15]',
                    ],
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
