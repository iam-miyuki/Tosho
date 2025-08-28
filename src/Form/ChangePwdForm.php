<?php

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


class ChangePwdForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password',PasswordType::class,[
                'required'=>true,
                'mapped'=>false,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez entrer votre mot de passe actuel'
                    ]),
                ],
            ])
            ->add('newPwd',PasswordType::class,[
                'required'=>true,
                'mapped'=>false,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez entrer un nouveau mot de passe'
                    ]),
                    new Assert\Length([
                        'min' => 8,
                        'minMessage' => 'Le nouveau mot de passe doit contenir au moins 8 caractères',
                    ]),
                ],
            ])
            ->add('confirm',PasswordType::class,[
                'required'=>true,
                'mapped'=>false,
                 'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez confirmer le nouveau mot de passe'
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null
        ]);
    }
}
