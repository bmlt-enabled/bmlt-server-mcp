COMMIT := $(shell git rev-parse --short=8 HEAD 2>/dev/null || echo dev)
VERSION := $(or $(VERSION), $(shell git describe --tags --always 2>/dev/null || echo dev))
BASENAME := $(shell basename $(PWD))
BUILD_DIR := $(or $(BUILD_DIR), build)
ZIP_FILE := $(BUILD_DIR)/$(BASENAME)-$(VERSION).zip
STAGING := $(BUILD_DIR)/$(BASENAME)-$(VERSION)
VENDOR_AUTOLOAD := vendor/autoload.php
ENV_FILE := .env
IMAGE := $(BASENAME)
TAG := local
PORT ?= 8080

ifeq ($(PROD)x, x)
	COMPOSER_ARGS := --prefer-dist --no-progress --no-interaction
else
	COMPOSER_ARGS := --no-dev --prefer-dist --no-progress --no-interaction --optimize-autoloader
endif

help:  ## Print the help documentation
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

$(VENDOR_AUTOLOAD):
	composer install $(COMPOSER_ARGS)

$(ENV_FILE):
	cp .env.example .env
	php artisan key:generate --ansi

.PHONY: composer
composer: $(VENDOR_AUTOLOAD)  ## Runs composer install (PROD=1 for --no-dev)

.PHONY: build
build: $(VENDOR_AUTOLOAD) $(ENV_FILE)  ## Install deps and prep .env

.PHONY: dev
dev:  ## Docker compose up (FrankenPHP)
	MCP_PORT=$(PORT) docker compose up --build

.PHONY: serve
serve: $(VENDOR_AUTOLOAD) $(ENV_FILE)  ## Run php artisan serve on $PORT (default 8080)
	php -d display_errors=stderr artisan serve --port=$(PORT)

.PHONY: inspect
inspect: $(VENDOR_AUTOLOAD)  ## Open the MCP Inspector
	php artisan mcp:inspector

.PHONY: bash
bash:  ## Bash shell in the running `make dev` container
	docker compose exec mcp sh

.PHONY: logs
logs:  ## Tail logs from `make dev`
	docker compose logs -f mcp

.PHONY: test
test: $(VENDOR_AUTOLOAD)  ## Run PHP tests
	php artisan test

.PHONY: lint
lint: $(VENDOR_AUTOLOAD)  ## Pint --test
	vendor/bin/pint --test

.PHONY: fmt
fmt: $(VENDOR_AUTOLOAD)  ## Pint (auto-fix)
	vendor/bin/pint

$(ZIP_FILE):
	@rm -rf $(STAGING)
	mkdir -p $(STAGING)
	rsync -a \
		--exclude='.git' --exclude='.github' --exclude='vendor' --exclude='node_modules' \
		--exclude='tests' --exclude='build' \
		--exclude='storage/logs/*' \
		--exclude='storage/framework/cache/data/*' \
		--exclude='storage/framework/sessions/*' \
		--exclude='storage/framework/views/*' \
		--exclude='storage/framework/testing/*' \
		--exclude='.env' --exclude='.env.local' --exclude='.env.testing' \
		--exclude='phpunit.xml' --exclude='pint.json' --exclude='Makefile' \
		--exclude='.editorconfig' --exclude='.gitignore' --exclude='.gitattributes' --exclude='.dockerignore' \
		./ $(STAGING)/
	cd $(STAGING) && composer install --no-dev --prefer-dist --no-progress --no-interaction --optimize-autoloader
	cd $(BUILD_DIR) && zip -qr $(shell basename $(ZIP_FILE)) $(shell basename $(STAGING))
	@rm -rf $(STAGING)
	@shasum -a 256 $(ZIP_FILE) | tee $(ZIP_FILE).sha256
	@ls -lh $(ZIP_FILE)

.PHONY: zip
zip: $(ZIP_FILE)  ## Build release zip (vendor bundled, no-dev)

.PHONY: docker
docker:  ## Build the production Docker image
	docker build \
		--label "org.opencontainers.image.revision=$(COMMIT)" \
		--label "org.opencontainers.image.created=$(shell date -u +'%Y-%m-%dT%H:%M:%SZ')" \
		-t $(IMAGE):$(TAG) .

.PHONY: docker-push
docker-push:  ## Push docker image
	docker push $(IMAGE):$(TAG)

.PHONY: clean
clean:  ## Remove build artifacts
	rm -rf $(BUILD_DIR) vendor
	rm -f storage/logs/*.log
	rm -rf storage/framework/cache/data/*
	rm -rf storage/framework/sessions/*
	rm -rf storage/framework/views/*
