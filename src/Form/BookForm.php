<?php

namespace App\Form;

use App\Entity\Book;
use App\Enum\LocationEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class BookForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre : '
            ])
            ->add('author', TextType::class, [
                'label' => 'Auteur : '
            ])
            ->add('jpTitle', TextType::class, [
                'label' => 'Titre en japonais'
            ])
            ->add('jpAuthor', TextType::class, [
                'label' => 'Auteur en japonais'
            ])
            ->add('coverUrl', UrlType::class, [
                'label' => 'URL Couverture : '
            ])
            ->add('location', EnumType::class, [
                'class' => LocationEnum::class,
                'label' => 'Bâtiment : '
            ])
            ->add('note', TextareaType::class, [
                'required' => false
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
