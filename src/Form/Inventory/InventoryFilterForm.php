<?php

namespace App\Form;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Enum\InventoryStatusEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class InventoryFilterForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', EnumType::class, [
                'class' => InventoryStatusEnum::class,
                'label' => 'Statut : ',
                'placeholder' => 'Tous les statuts',
                'required' => false
            ])
            ->add('date', DateType::class, [
                'widget' => 'choice',
                'label' => 'Date : ',
                'placeholder' => ' -',
                'required' => false
            ])
            ->add('location', EnumType::class, [
                'class' => LocationEnum::class,
                'label' => 'Bâtiment : ',
                'placeholder' => 'Toute les bâtiments',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Inventory::class,
        ]);
    }
}
