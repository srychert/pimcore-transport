<?php

namespace App\Form;

use Pimcore\Model\DataObject\Airplane;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class TransportSubmitFormType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $airplanes = new Airplane\Listing();
        $airplaneChoices = [];
        foreach ($airplanes as $airplane) {
            $airplaneChoices[$airplane->getName()] = $airplane->getId();
        }
        ksort($airplaneChoices);

        $builder
            ->add('to', TextType::class, [
                'label' => 'general.to',
                'required' => true,
                'constraints' => [new NotBlank()]
            ])
            ->add('from', TextType::class, [
                'label' => 'general.from',
                'required' => true,
                'constraints' => [new NotBlank()]
            ])
            ->add('airplane', ChoiceType::class, [
                'label' => 'general.airplane',
                'required' => true,
                'choices' => $airplaneChoices
            ])
            ->add('date', DateType::class, [
                'label' => 'general.transportDate',
                'required' => true,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'constraints' => [new GreaterThan('today')]
            ]);

        $builder
            ->add('_submit', SubmitType::class, [
                'label' => 'general.submit',
                'attr' => [
                    'class' => 'btn btn-block btn-success'
                ]
            ]);
    }
}
