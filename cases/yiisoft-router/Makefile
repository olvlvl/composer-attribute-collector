COPY=composer-attribute-collector/

vendor:
	@composer install

.PHONY: plugin-copy
plugin-copy:
	mkdir -p $(COPY)
	cp -R ../../src $(COPY)
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
