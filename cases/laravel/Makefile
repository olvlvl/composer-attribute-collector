# do not edit the following lines

vendor:
	@composer install --prefer-lowest

.PHONY: test-container
test-container: test-container82

.PHONY: test-container82
test-container82:
	@-docker compose run --rm app82 bash
	@docker compose down -v

.PHONY: test-container84
test-container84:
	@-docker compose run --rm app84 bash
	@docker compose down -v
