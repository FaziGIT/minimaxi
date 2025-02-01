<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\ImageProduct;
use App\Entity\Product;
use App\Enum\SizeProductEnum;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Service\createImgFromProductForm;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    public function __construct(private createImgFromProductForm $createImgFromProductForm)
    {
    }

    #[Route('/', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

}
