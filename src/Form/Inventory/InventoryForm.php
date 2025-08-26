<?php

namespace App\Form\Inventory;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Enum\InventoryStatusEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class InventoryForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('location', EnumType::class, [
                'class' => LocationEnum::class,
                'label' => 'Lieu : ',
                'choice_label' => fn($choice) => $choice->value,

            ])
            ->add('note', TextareaType::class, [
                'label' => 'Commentaire (faculatif) : ',

            ])
            ->add('date', DateType::class, [
                'label' => 'Date d\'inventaire',

            ])
            ->add('status', EnumType::class, [
                'class' => InventoryStatusEnum::class,
                'label' => 'Statut : ',
                'choice_label' => fn($choice) => $choice->value,

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
