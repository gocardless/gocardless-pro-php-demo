PORT=8080

vendor:
	composer install

server: vendor
	php -S localhost:$(PORT) -t public/