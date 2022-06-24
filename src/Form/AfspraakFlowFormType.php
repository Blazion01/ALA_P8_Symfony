<?php

namespace App\Form;

use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use App\Form\AfspraakFormType;

class AfspraakFlowFormType extends FormFlow
{
    // private $transport;

    // public function __construct($transport)
    // {
    //     $this->transport = $transport;
    // }

    // protected $currentStepNumber = 1;

    protected $allowDynamicStepNavigation = true;
    
    protected function loadStepsConfig() {
        return [
            [
                'label' => 'Dienst(en)',
                'form_type' => AfspraakFormType::class,
            ],
            [
                'label' => 'Behandeling(en)',
                'form_type' => AfspraakFormType::class,
            ],
            [
                'label' => 'Datum',
                'form_type' => AfspraakFormType::class,
            ],
            [
                'label' => 'Tijd',
                'form_type' => AfspraakFormType::class,
            ],
            [
                'label' => 'Afrekenen',
            ],
            [
                'label' => 'Bevestiging',
            ],
        ];
    }
}

// $containerBuilder = new ContainerBuilder();
// $containerBuilder->setParameter('maakAfspraak.transport', 'maakAfspraak');
// $containerBuilder
//     ->register('afspraak.form.flow.createAfspraak', 'AfspraakFlowFormType')
//     ->addArgument('%maakAfspraak.transport%');

// $containerBuilder
//     ->register('customer_controller', 'CustomerController')
//     ->addArgument(new Reference('afspraak.form.flow.createAfspraak'));
