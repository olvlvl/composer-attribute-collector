COPY=composer-attribute-collector/

vendor:
	@composer install --prefer-lowest

.PHONY: plugin-copy
plugin-copy:
	cp -r ../../src $(COPY)
	cp ../../composer.json $(COPY)
	cp ../../collector.php $(COPY)

.PHONY: test-container
test-container: test-container80

.PHONY: test-container80
test-container80:
	@-docker compose run --rm app80 bash
	@docker compose down -v

.PHONY: test-container81
test-container81:
	@-docker-compose run --rm app81 bash
	@docker-compose down -v

.PHONY: test-container82
test-container82:
	@-docker compose run --rm app82 bash
	@docker compose down -v

.PHONY: test-container84
test-container84:
	@-docker compose run --rm app84 bash
	@docker compose down -v
