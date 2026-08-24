COPY=composer-attribute-collector/

vendor:
	@composer install --prefer-lowest

.PHONY: plugin-copy
plugin-copy:
	cp -r ../../src $(COPY)
	cp ../../composer.json $(COPY)
	cp ../../collector.php $(COPY)

.PHONY: test-container
test-container: test-container82

.PHONY: test-container82
test-container82: plugin-copy
	@-docker compose run --rm app82 bash
	@docker compose down -v

.PHONY: test-container84
test-container84: plugin-copy
	@-docker compose run --rm app84 bash
	@docker compose down -v
