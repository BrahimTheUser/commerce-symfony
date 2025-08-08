<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Order;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', null, [
                'label' => 'firstName',
                'attr' => ['class' => 'form form-control' , 'placeholder' => 'firstName...']
            ])
            ->add('lastName', null, [
                'label' => 'lastName',
                'attr' => ['class' => 'form form-control' , 'placeholder' => 'lastName...']
            ])
            ->add('email', null, [
                'label' => 'Email',
                'attr' => ['class' => 'form form-control' , 'placeholder' => 'email...']
            ])
            ->add('phone', null, [
                'label' => 'phone',
                'attr' => ['class' => 'form form-control' , 'placeholder' => 'phone...']
            ])
            ->add('adresse', null, [
                'label' => 'adresse',
                'attr' => ['class' => 'form form-control' , 'placeholder' => 'adresse...']
            ])
            // ->add('createAt', null, [
            //     'widget' => 'single_text',
            //     'attr' => ['class' => 'form-control'],
            // ])
            ->add('city', EntityType::class, [
                'class' => City::class,
                'choice_label' => 'name',
                'attr' => ['class' => 'form-select'],
                'label' => 'Ville',
                'label_attr' => ['class' => 'form-label mt-4']
            ])
            ->add('payOnDelivery', null, [
                'label' => 'payer à la livraison',
                'attr' => ['class' => 'mt-3']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
