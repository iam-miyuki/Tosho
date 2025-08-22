<?php

namespace App\Form\Inventory;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Enum\InventoryStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;

class InventoryFilterForm extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('status', EnumType::class, [
                'class' => InventoryStatusEnum::class,
                'label' => 'Statut : ',
                'placeholder' => 'Tout',
                'choice_label' => fn($choice) => $choice->value,
                'required' => false
            ])

            ->add('location', EnumType::class, [
                'class' => LocationEnum::class,
                'label' => 'Lieu : ',
                'placeholder' => 'Tout',
                'choice_label' => fn($choice) => $choice->value,
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
