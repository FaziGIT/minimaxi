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
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Type;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => ['class' => 'floating-input'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez renseigner un nom'),
                ],
            ])
            ->add('description', TextType::class, [
                'label' => 'Description du produit',
                'attr' => ['class' => 'floating-input'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez renseigner une description'),
                ],
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix du produit',
                'attr' => ['class' => 'floating-input', 'step' => '0.1'],
                'invalid_message' => 'Le prix doit être un nombre décimal',
                'constraints' => [
                    new NotBlank(message: 'Veuillez renseigner un prix'),
                ]
            ])
            ->add('stockQuantity', IntegerType::class, [
                'label' => 'Quantité en stock',
                'attr' => ['class' => 'floating-input'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez renseigner une quantité'),
                    new PositiveOrZero(message: 'La quantité doit être un nombre positif ou nul'),
                ]
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
