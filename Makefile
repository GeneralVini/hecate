.PHONY: setup install qa qa-all qa-fix lint lint-fix stan psalm test

setup:
	./scripts/bootstrap.sh

install:
	composer install

qa:
	composer qa

qa-all:
	composer qa:all

qa-fix:
	composer qa:fix

lint:
	composer lint

lint-fix:
	composer lint:fix

stan:
	composer stan

psalm:
	composer psalm

test:
	composer test
