.PHONY: setup install qa lint stan psalm test

setup:
	./scripts/bootstrap.sh

install:
	composer install

qa:
	composer qa

lint:
	composer lint

stan:
	composer stan

psalm:
	composer psalm

test:
	composer test
