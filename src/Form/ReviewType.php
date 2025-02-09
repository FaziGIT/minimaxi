<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Product;
use App\Entity\Review;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Écrire un commentaire',
                    'class' => 'px-0 w-full text-sm text-gray-900 border-0 focus:ring-0 focus:outline-none'
                ],
            ])
            ->add('rating', IntegerType::class, [
                'label' => 'Veuillez noter le produit entre 0 et 5',
                'attr' => [
                    'min' => 0,
                    'max' => 5,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
