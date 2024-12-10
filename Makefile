# Variables pour les noms des conteneurs
PHP_CONTAINER=minimaxi-php-1
NODE_CONTAINER=minimaxi-node-1

# Commande par défaut
.PHONY: help
help:
	@echo "Commandes disponibles :"
	@echo "  make symfony [cmd] [args...]   - Exécuter une commande Symfony (php bin/console)"
	@echo "  make npm [cmd] [args...]       - Exécuter une commande npm (rajouter -- si vous ajoutez des arguments)"
	@echo "  make up                       - Démarrer les conteneurs avec 'docker-compose up'"
	@echo "  make up-build                 - Démarrer les conteneurs avec 'docker-compose up --build'"
	@echo "  make up-detached              - Démarrer les conteneurs en mode détaché ('docker-compose up -d')"
	@echo "  make down                     - Arrêter et supprimer les conteneurs avec 'docker-compose down'"
	@echo "Exemples :"
	@echo "  make symfony make:controller xxx"
	@echo "  make npm run watch"
	@echo "  make npm -- -v"
	@echo "  make npx tailwindcss init -p"
	@echo "  make up"
	@echo "  make up-build"
	@echo "  make down"

# Exécuter une commande Symfony (php bin/console)
.PHONY: symfony
symfony:
	@docker exec -it $(PHP_CONTAINER) php bin/console $(filter-out $@,$(MAKECMDGOALS))

# Exécuter une commande npm
.PHONY: npm
npm:
	@docker exec -it $(NODE_CONTAINER) npm $(filter-out $@,$(MAKECMDGOALS))

# Exécuter une commande npm (tailwind)
.PHONY: npx
npx:
	@docker exec -it $(NODE_CONTAINER) npx $(filter-out $@,$(MAKECMDGOALS))

.PHONY: composer
composer:
	@docker exec -it $(PHP_CONTAINER) composer $(filter-out $@,$(MAKECMDGOALS))

# Démarrer les conteneurs avec 'docker-compose up --build'
.PHONY: up-build
up-build:
	@docker compose up --build -d

# Démarrer les conteneurs en mode détaché ('docker-compose up -d')
.PHONY: up
up:
	@docker compose up -d

# Arrêter et supprimer les conteneurs avec 'docker-compose down'
.PHONY: down
down:
	@docker compose down

.PHONY: fixtures
fixtures:
	@docker exec -it $(PHP_CONTAINER) php bin/console d:f:l --no-interaction

.PHONY: composer-install
composer-install:
	@docker exec -it $(PHP_CONTAINER) composer install

# Éviter les erreurs pour les cibles dynamiques
%:
	@:
