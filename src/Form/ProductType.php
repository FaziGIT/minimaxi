<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use App\Enum\SizeProductEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => ['class' => 'floating-input'],
            ])
            ->add('description', TextType::class, [
                'label' => 'Description du produit',
                'attr' => ['class' => 'floating-input'],
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix du produit',
                'attr' => ['class' => 'floating-input', 'step' => '0.1'],
            ])
            ->add('stockQuantity', IntegerType::class, [
                'label' => 'Quantité en stock',
                'attr' => ['class' => 'floating-input'],
            ])
            ->add('size', ChoiceType::class, [
                'choices' => [
                    'Mini' => SizeProductEnum::MINI,
                    'Maxi' => SizeProductEnum::MAXI,
                ],
                'label' => 'Taille du produit',
                'attr' => ['class' => 'floating-select'],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'attr' => ['class' => 'floating-select'],
            ])
            ->add('imageProducts', CollectionType::class, [
                'entry_type' => ImageProductType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'prototype' => true,
                'entry_options' => [
                    'label' => false  // Ceci cachera les labels de chaque élément
                ],
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
