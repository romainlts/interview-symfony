<?php

namespace App\Form\Security;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class LoginType extends AbstractType
{
    /*
    * Build the login form
    *
    * @param FormBuilderInterface $builder
    * @param array $options
    * @return void
    */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', TextType::class, ['attr' => ['placeholder' => 'Email']])
            ->add('password', PasswordType::class, ['attr' => ['placeholder' => 'Password']])
            ->add('submit', SubmitType::class, ['label' => 'Sign in'])
            ->setAction($options['action'])
        ;
    }

    /*
    * Configure options for the form
    *
    * @param OptionsResolver $resolver
    * @return void
    */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'action' => null,
        ]);
    }

    /*
    * Disable the form name prefix
    * 
    * @return string
    */
    public function getBlockPrefix(): string
    {
        return '';
    }
}
