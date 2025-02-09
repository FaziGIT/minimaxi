# Cahier des charges - Plateforme E-commerce Mini Maxi

## Présentation du projet
Mini Maxi est une plateforme e-commerce spécialisée dans la vente d'articles de deux gammes distinctes : Mini (petites tailles) et Maxi (grandes tailles). Le projet offre une expérience d'achat complète avec des fonctionnalités sociales et un système de fidélisation.

## Objectifs du projet
- Offrir une plateforme e-commerce dédiée aux tailles Mini et Maxi
- Créer une expérience utilisateur sociale et engageante
- Permettre une gestion efficace des commandes et du stock
- Fidéliser les clients via un système de codes promo

## Fonctionnalités principales

### Gestion des utilisateurs
- Système d'authentification complet
- Profils utilisateurs détaillés
  - Informations personnelles
  - Historique des commandes
  - Adresse de livraison
  - Points de fidélité
  - Mes codes promos
- Différents rôles (Client, Admin, Banni)

### Catalogue et produits
- Gestion complète des produits
  - Catégorisation
  - Images multiples
  - Stock en temps réel
  - Prix
- Filtrage et tri des produits
  - Par prix
- Notation et avis produits

### Système de commande
- Panier d'achat dynamique
  - Ajout/suppression de produits
  - Modification des quantités
  - Calcul automatique des totaux
- Application de codes promo
- Suivi des commandes
  - États multiples (En attente, Payée, Expédiée, etc.)
  - Historique détaillé

### Fonctionnalités sociales
- Système de wishlist
  - Ajout/retrait de produits
- Avis et notations
  - Commentaires sur les produits
  - Système de likes sur les avis

### Administration
- Gestion des produits
  - Ajout/modification/suppression
  - Gestion des stocks
- Gestion des catégories
  - Ajout/modification/suppression
- Gestion des code promos
  - Ajout/modification/suppression
- Modération des utilisateurs
  - Bannissement

## Technologies utilisées
- Backend: Symfony 6.3
- Frontend: Twig + VueJS
- Base de données: PostgreSQL
- Sécurité: système de rôles et tokens CSRF

## Base de données
### Entités principales
- User/Client (gestion utilisateurs)
- Product (catalogue produits)
- Category (catégories)
- Order/OrderItem (commandes)
- Review (avis)
- Wishlist (liste de souhaits)
- Like (interactions sociales)
- DiscountCode (codes promo)

## Statuts commande
- PENDING (en attente)
- PAID (payée)
- SHIPPED (expédiée)
- DELIVERED (livrée)
- CANCELLED (annulée)

## Types de tailles
- MINI
- MAXI