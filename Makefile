# Backend Makefile for GymGest
# Docker-based commands for Laravel backend

.PHONY: help up down restart logs shell test migrate seed fresh db-clean db-fresh composer-install composer-update cache-clear swagger

# Variables
DOCKER_COMPOSE = docker-compose
SERVICE_NAME = backend
CONTAINER_NAME = gymgest_backend

# Default target
help:
	@echo "Backend Management Commands:"
	@echo "  make up               - Start backend container"
	@echo "  make down             - Stop backend container"
	@echo "  make restart          - Restart backend container"
	@echo "  make logs             - View backend container logs"
	@echo "  make shell            - Access backend container shell"
	@echo "  make test             - Run PHPUnit tests"
	@echo "  make migrate          - Run database migrations"
	@echo "  make seed             - Run database seeders"
	@echo "  make fresh            - Fresh database (drop, migrate, seed)"
	@echo "  make db-clean         - [DESTRUCTIVE] Truncate all tables (keeps schema)"
	@echo "  make db-fresh         - [DESTRUCTIVE] Drop DB, recreate and run migrations"
	@echo "  make composer-install - Install composer dependencies"
	@echo "  make composer-update  - Update composer dependencies"
	@echo "  make cache-clear      - Clear Laravel cache"
	@echo "  make swagger          - Regenerate Swagger documentation"

# Docker container management
up:
	@echo "Starting backend container..."
	@cd .. && $(DOCKER_COMPOSE) up -d $(SERVICE_NAME)
	@echo "Backend is running on port 9000"

down:
	@echo "Stopping backend container..."
	@cd .. && $(DOCKER_COMPOSE) stop $(SERVICE_NAME)

restart:
	@echo "Restarting backend container..."
	@cd .. && $(DOCKER_COMPOSE) restart $(SERVICE_NAME)

logs:
	@cd .. && $(DOCKER_COMPOSE) logs -f $(SERVICE_NAME)

shell:
	@docker exec -it $(CONTAINER_NAME) /bin/bash

# Testing
test:
	@echo "Running PHPUnit tests..."
	@docker exec $(CONTAINER_NAME) ./vendor/bin/phpunit

# Database operations
migrate:
	@echo "Running migrations..."
	@docker exec $(CONTAINER_NAME) php artisan migrate

seed:
	@echo "Running seeders..."
	@docker exec $(CONTAINER_NAME) php artisan db:seed

fresh:
	@echo "Refreshing database..."
	@docker exec $(CONTAINER_NAME) php artisan migrate:fresh --seed

# Database cleanup operations
# WARNING: These commands are DESTRUCTIVE and will delete data
db-clean:
	@echo "⚠️  WARNING: This will truncate ALL tables (data deleted, schema preserved)"
	@echo "Running db:wipe (truncates all tables)..."
	@docker exec $(CONTAINER_NAME) php artisan db:wipe --force
	@echo "✓ All tables truncated. Run 'make migrate' to restore schema if needed."

db-fresh:
	@echo "⚠️  WARNING: This will DROP the entire database and recreate it from migrations"
	@echo "Running migrate:fresh (drops all tables and re-runs migrations)..."
	@docker exec $(CONTAINER_NAME) php artisan migrate:fresh --force
	@echo "✓ Database recreated from scratch. Run 'make seed' if you need test data."

# Composer operations
composer-install:
	@echo "Installing composer dependencies..."
	@docker exec $(CONTAINER_NAME) composer install

composer-update:
	@echo "Updating composer dependencies..."
	@docker exec $(CONTAINER_NAME) composer update

# Laravel cache
cache-clear:
	@echo "Clearing Laravel cache..."
	@docker exec $(CONTAINER_NAME) php artisan cache:clear
	@docker exec $(CONTAINER_NAME) php artisan config:clear
	@docker exec $(CONTAINER_NAME) php artisan route:clear
	@docker exec $(CONTAINER_NAME) php artisan view:clear

# Swagger/OpenAPI
swagger:
	@echo "Regenerating Swagger documentation..."
	@docker exec $(CONTAINER_NAME) php artisan l5-swagger:generate
