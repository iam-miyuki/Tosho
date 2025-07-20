<?php

namespace App\Form;

use App\Entity\Book;
use App\Entity\User;
use App\Entity\Inventory;
use App\Entity\InventoryItem;
use App\Enum\InventoryItemStatusEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class InventoryItemForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', EnumType::class,[
                'class'=> InventoryItemStatusEnum::class,
                'label'=> 'Status'
            ])
            ->add('note', TextareaType::class,[
                'label'=> 'Commentaire',
                'required' =>false
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
