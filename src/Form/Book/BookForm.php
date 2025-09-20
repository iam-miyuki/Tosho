<?php

namespace App\Form\Book;

use App\Entity\Book;
use App\Enum\LocationEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class BookForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre : ',
                'required' => false
            ])
            ->add('author', TextType::class, [
                'label' => 'Auteur : ',
                'required' => false
            ])
            ->add('jpTitle', TextType::class, [
                'label' => 'Titre en japonais : ',
                'required' => false
            ])
            ->add('jpAuthor', TextType::class, [
                'label' => 'Auteur en japonais : ',
                'required' => false
            ])
            ->add('coverUrl', UrlType::class, [
                'label' => 'URL Couverture : ',
                'required' => false
            ])
            ->add('location', EnumType::class, [
                'class' => LocationEnum::class,
                'label' => 'Lieu : ',
                'placeholder' => 'Choisissez un Lieu',
                'choice_label' => fn($choice) => $choice->value,
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
