<?php

namespace App\Form;

use App\Entity\InventoryItem;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName',TextType::class,[
                'label'=>'Nom : ',
                'require'=>false
            ])
            ->add('lastName',TextType::class,[
                'label'=>'Prénom : ',
                'require'=>false
            ])
            ->add('jpLastName',TextType::class,[
                'label'=>'Nom en japonais : ',
                'require'=>false
            ])
            ->add('jpFirstName',TextType::class,[
                'label'=>'Prénom en japonais : ',
                'require'=>false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
