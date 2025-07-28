<?php

namespace App\Form;

use App\Entity\Family;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class FamilyForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de famille : '
            ])
            ->add('jpName', TextType::class, [
                'label' => 'Nom de famille en japonais : '
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email : '
            ])
            ->add('members', CollectionType::class, [
                'entry_type' => MemberForm::class,
                'label' => 'Enfants',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false, // pour que Doctrine détecte les ajouts/suppressions
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Family::class,
        ]);
    }
}
