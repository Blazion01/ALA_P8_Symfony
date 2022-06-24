<?php

namespace App\Form;

use App\Entity\Behandeling;
use App\Repository\BehandelingRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AfspraakFormType extends AbstractType
{
    private $BR;

    public function __construct(BehandelingRepository $BR)
    {
        $this->BR = $BR;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        switch ($options['flow_step']) {
            case 1:
                $builder
                    ->add('BehandelingType', ChoiceType::class, [
                        'placeholder' => 'Selecteer een dienst',
                        'choices' => [
                            'Nagels' => 'N',
                            'Haar' => 'H',
                        ],
                        // 'mapped' => false,
                    ])
                ;
                break;
            
            case 2:
                $builder
                    ->add('BehandelingType', HiddenType::class)
                    ->add('behandeling', EntityType::class, [
                        'placeholder' => 'Selecteer een Behandeling',
                        'class' => Behandeling::class,
                        'label' => 'Selecteer een Behandeling',
                        'query_builder' => $this->BR->findAllQB(),
                        'group_by' => function($choice, $key, $value) {
                            $groep = $choice->getGroep();
                            if ($groep == "NN") {
                                return "Nagels | Nieuwe Set";
                            }
                            if ($groep == "NA") {
                                return "Nagels | Nabehandeling";
                            }
                            if ($groep == "NH") {
                                return "Nagels | Handen";
                            }
                            if ($groep == "NV") {
                                return "Nagels | Voeten";
                            }
                            if ($groep == "HD") {
                                return "Haar | Dames";
                            }
                            if ($groep == "HH") {
                                return "Haar | Heren";
                            }
                            if ($groep == "HK") {
                                return "Haar | Kinderen t/m 11 jaar";
                            }
                            if ($groep == "HT") {
                                return "Haar | Kinderen 12 t/m 15 jaar";
                            }
                            return $groep;
                        },
                        'choice_label' => 'naam',
                    ]);
                break;
            case 3:
                $builder
                    ->add('datum', DateType::class, [
                        'placeholder' => 'Kies een datum',
                        'widget' => 'single_text',
                    
                        // prevents rendering it as type="date", to avoid HTML5 date pickers
                        'html5' => false,
                    
                        // adds a class that can be selected in JavaScript
                        'attr' => ['class' => 'js-datepicker'],
                    ]);
                break;
            case 4:
                $builder
                    ->add('tijd', TimeType::class , [
                        'placeholder' => 'Kies een tijd',
                        'input' => 'datetime',
                        'widget' => 'choice',
                        'hours' => [
                            10,
                            11,
                            12,
                            13,
                            14,
                            15,
                            16,
                            17,
                            19,
                            20,
                        ],
                        'minutes' => [
                            0,
                            30,
                        ],
                        'seconds' => [
                            0,
                        ],
                    ])
                    ->add('datum', DateType::class, [
                        'placeholder' => 'Kies een datum',
                        'widget' => 'single_text',
                    
                        // prevents rendering it as type="date", to avoid HTML5 date pickers
                        'html5' => false,
                    
                        // adds a class that can be selected in JavaScript
                        'attr' => ['class' => 'disabled'],
                        'label_attr' => ['class' => 'disabled'],
                    ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
