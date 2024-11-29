ARG PHP_TAG=8.0-cli-buster
FROM php:${PHP_TAG}

RUN <<-EOF
	apt-get update
	apt-get install -y autoconf pkg-config
	pecl channel-update pecl.php.net
	pecl install xdebug
	docker-php-ext-enable opcache xdebug
EOF

RUN <<-EOF
	cat <<-SHELL >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
	xdebug.client_host=host.docker.internal
	xdebug.mode=develop
	xdebug.start_with_request=yes
	SHELL

	cat <<-SHELL >> /usr/local/etc/php/conf.d/php.ini
	display_errors=On
	error_reporting=E_ALL
	date.timezone=UTC
	SHELL
EOF

ENV COMPOSER_ALLOW_SUPERUSER 1

RUN <<-EOF
	apt-get update
	apt-get install unzip
	curl -s https://raw.githubusercontent.com/composer/getcomposer.org/76a7060ccb93902cd7576b67264ad91c8a2700e2/web/installer | php -- --quiet
	mv composer.phar /usr/local/bin/composer
	cat <<-SHELL >> /root/.bashrc
	export PATH="$HOME/.composer/vendor/bin:$PATH"
	SHELL
EOF

RUN composer global require squizlabs/php_codesniffer
