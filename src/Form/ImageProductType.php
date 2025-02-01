<?php

namespace App\Form;

use App\Entity\ImageProduct;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class ImageProductType extends AbstractType
{
    public function __construct(private RouterInterface $router)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $this->router->getRouteCollection()->get('app_admin_newproduct');

        $builder
            ->add('image', FileType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'mb-2 w-[100px]'
                ]
            ]);

        if ($isEdit) {
            $builder->add('url', HiddenType::class, [
                'label' => false,
                'required' => false,
                'disabled' => true,
                'attr' => [
                    'class' => 'mb-2'
                ]
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ImageProduct::class,
        ]);
    }
}
