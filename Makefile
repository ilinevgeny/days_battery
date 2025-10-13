.PHONY: help up down build install shell composer console test fix check migrate db-create db-drop

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

up: ## Start containers
	docker-compose up -d

down: ## Stop containers
	docker-compose down

build: ## Build containers
	docker-compose build --no-cache

install: ## Install dependencies
	docker-compose exec php composer install

shell: ## Enter PHP container shell
	docker-compose exec php sh

composer: ## Run composer command (use: make composer CMD="require vendor/package")
	docker-compose exec php composer $(CMD)

console: ## Run Symfony console command (use: make console CMD="debug:router")
	docker-compose exec php php bin/console $(CMD)

test: ## Run tests
	docker-compose exec php vendor/bin/phpunit

fix: ## Fix code style
	docker-compose exec php composer fix

check: ## Check code quality (PHPStan + PHP CS Fixer dry-run)
	docker-compose exec php composer check

migrate: ## Run database migrations
	docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction

db-create: ## Create database
	docker-compose exec php php bin/console doctrine:database:create --if-not-exists

db-drop: ## Drop database
	docker-compose exec php php bin/console doctrine:database:drop --force --if-exists

logs: ## Show container logs
	docker-compose logs -f

restart: ## Restart containers
	docker-compose restart
