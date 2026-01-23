<?php

namespace App\Form\Entity;

use App\Entity\Beneficiary;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class BeneficiaryType extends AbstractType
{
    /*
    * Build the beneficiary form
    *
    * @param FormBuilderInterface $builder
    * @param array $options
    * @return void
    */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Name'])
            ->add('submit', SubmitType::class)
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
            'data_class' => Beneficiary::class,
        ]);
    }
}
