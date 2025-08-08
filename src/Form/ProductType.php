<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\SubCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter product name'],
                'label' => 'Product Name',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter product description', 'rows' => 4],
                'label' => 'Description',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('price', MoneyType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter product price'],
                'label' => 'Price',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'stock',
                'attr' => ['class' => 'form form-control'],
                'mapped' => false,
            ])
            ->add('image', FileType::class, [
                'label' => 'Image de produit',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                       'mimeTypes' => ['image/jpeg', 'image/png', 'image/jpg'],
                       'mimeTypesMessage' => 'Please upload a valid image file (jpeg, png, jpg).',
                       'maxSizeMessage' => 'The file is too large. Maximum allowed size is 1MB.'
                    ])
                ]
            ])
            ->add('subCategories', EntityType::class, [
                'class' => SubCategory::class,
                'choice_label' => 'name',
                'multiple' => true,
                'attr' => ['class' => 'form-select'],
                'label' => 'Sub Categories',
                'label_attr' => ['class' => 'form-label']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
