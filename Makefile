# customization

PACKAGE_NAME = olvlvl/composer-attribute-collector
PHPUNIT = vendor/bin/phpunit

# do not edit the following lines

vendor:
	@composer install

# testing

.PHONY: test-dependencies
test-dependencies: vendor test-cleanup

.PHONY: test
test: test-dependencies
	@$(PHPUNIT)

.PHONY: test-coverage
test-coverage: test-dependencies
	@mkdir -p build/coverage
	@XDEBUG_MODE=coverage $(PHPUNIT) --coverage-html build/coverage

.PHONY: test-coveralls
test-coveralls: test-dependencies
	@mkdir -p build/logs
	@XDEBUG_MODE=coverage $(PHPUNIT) --coverage-clover build/logs/clover.xml

.PHONY: test-cleanup
test-cleanup:
	@rm -rf .composer-attribute-collector/*
	@rm -rf tests/sandbox/*

.PHONY: test-container
test-container: test-container82

.PHONY: test-container80
test-container80:
	@-docker-compose run --rm app80 bash
	@docker-compose down -v

.PHONY: test-container82
test-container82:
	@-docker-compose run --rm app82 bash
	@docker-compose down -v

.PHONY: lint
lint:
	@XDEBUG_MODE=off phpcs -s
	@XDEBUG_MODE=off vendor/bin/phpstan
