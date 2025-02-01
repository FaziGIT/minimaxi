<?php

namespace App\Service;

use App\Entity\Product;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormInterface;

class createImgFromProductForm
{
    public function __construct(private ParameterBagInterface $params)
    {
    }

    public function createImage(Product $product, FormInterface $form, EntityManagerInterface $entityManager, ?ArrayCollection $originalImages = null): void
    {
        // Récupérer la collection d'images du formulaire
        $imageProducts = $form->get('imageProducts');

        // Gérer les images supprimées seulement si on a des images originales
        if ($originalImages !== null) {
            foreach ($originalImages as $originalImage) {
                if (false === $product->getImageProducts()->contains($originalImage)) {
                    // L'image a été supprimée du formulaire
                    if ($originalImage->getUrl() && file_exists($this->params->get('imagesProductDestination') . '/' . $originalImage->getUrl())) {
                        unlink($this->params->get('imagesProductDestination') . '/' . $originalImage->getUrl());
                    }
                    $entityManager->remove($originalImage);
                }
            }
        }

        // Traiter les images existantes et nouvelles
        foreach ($product->getImageProducts() as $index => $imageProduct) {
            $imageField = $imageProducts->get($index);

            // Si le champ image n'existe pas ou ne contient pas de données
            if (!$imageField->has('image') || $imageField->get('image')->getData() === null) {
                // Si c'est une nouvelle image sans fichier, on la supprime
                if (!$imageProduct->getUrl()) {
                    $product->getImageProducts()->removeElement($imageProduct);
                }
                // Si c'est une image existante (avec URL), on la garde
                continue;
            }

            // Process the new file
            $fichierImage = $imageField->get('image')->getData();

            // Si on a une nouvelle image et qu'il existe déjà une URL (cas de remplacement)
            if ($imageProduct->getUrl()) {
                // Supprimer l'ancien fichier
                $oldFilePath = $this->params->get('imagesProductDestination') . '/' . $imageProduct->getUrl();
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            // Generate a unique name for the file
            $fichier = md5(uniqid()) . '.' . $fichierImage->guessExtension();

            // Get the destination directory
            $uploadDir = $this->params->get('imagesProductDestination');

            // Move the file
            $fichierImage->move($uploadDir, $fichier);

            // Update the image URL
            $imageProduct->setUrl($fichier);
        }
    }
}
