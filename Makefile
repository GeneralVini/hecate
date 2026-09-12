.PHONY: setup install hooks fix qa lint rector stan psalm test

setup:
	./scripts/bootstrap.sh

install:
	composer install

hooks:
	lefthook install

fix:
	composer fix

qa:
	composer qa

lint:
	composer lint

rector:
	composer rector

stan:
	composer stan

psalm:
	composer psalm

test:
	composer test
