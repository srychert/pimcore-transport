<?php

namespace App\Form\Type;

use Pimcore\Model\DataObject\Cargo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class CargoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'general.cargo.name',
                'required' => true,
                'constraints' => [new NotBlank()]
            ])
            ->add('weight', NumberType::class, [
                'label' => 'general.cargo.weight',
                'required' => true,
                'constraints' => array_merge($options['cargo_constraints'], [new NotBlank(), new Positive()])
            ])
            ->add('cargoType', ChoiceType::class, [
                'label' => 'general.cargo.type',
                'choices'  => [
                    'cargo.normal' => 'normal',
                    'cargo.dangerous' => 'dangerous',
                ],
                'required' => true,
                'constraints' => [new NotBlank(), new Choice(['choices' => ['normal', 'dangerous']])]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cargo::class,
            'cargo_constraints' => []
        ]);
    }
}
