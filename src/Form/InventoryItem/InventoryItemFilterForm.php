<?php

namespace App\Form;

use App\Entity\Book;
use App\Entity\User;
use App\Entity\Inventory;
use App\Enum\LoanStatusEnum;
use App\Entity\InventoryItem;
use App\Enum\InventoryItemStatusEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class InventoryItemFilterForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status',EnumType::class,[
                'class'=>InventoryItemStatusEnum::class,
                'label'=>'Statut : '
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InventoryItem::class,
        ]);
    }
}
