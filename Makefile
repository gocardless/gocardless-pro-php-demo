PORT=8080
COMPOSER_COMMAND=./composer.phar

server: vendor
	php -S localhost:$(PORT) -t public/

vendor: composer.phar
	$(COMPOSER_COMMAND) install

composer.phar:
ifneq (,$(wildcard ./composer.phar))
	$(info using installed local composer)
else
ifneq (,$(shell which composer))
	ln -s composer.phar $(which composer)
	$(info using local composer)
else
	$(info downloading composer)
	curl -O https://getcomposer.org/composer.phar
	chmod u+x ./composer.phar
endif
endif
