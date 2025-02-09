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
            $imageField = $imageProducts->get(strval($index));

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

            // Générer un nom de fichier unique
            $fichier = md5(uniqid()) . '.' . $fichierImage->guessExtension();

            // Récupérer le répertoire d'upload
            $uploadDir = $this->params->get('imagesProductDestination');

            // Déplacer le fichier dans le répertoire d'upload
            $fichierImage->move($uploadDir, $fichier);

            // Mettre à jour l'entité avec le nom du fichier
            $imageProduct->setUrl($fichier);
        }
    }
}
