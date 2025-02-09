<?php

namespace App\Form;

use App\Entity\DiscountCode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class DiscountCodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code')
            ->add('percentage', NumberType::class, [
                'attr' => [
                    'min' => 0,
                    'max' => 100,
                ],
                'invalid_message' => 'Le pourcentage doit être un nombre.',
                'constraints' => [
                    new NotBlank(message: 'Veuillez renseigner un pourcentage'),
                    new PositiveOrZero(message: 'Le pourcentage doit être un nombre positif ou nul'),
                ],
            ])
            ->add('validUntil', DateType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DiscountCode::class,
        ]);
    }
}
