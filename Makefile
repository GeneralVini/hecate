.PHONY: setup install hooks fix qa check security security-dast lint rector stan psalm psalm-taint test

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

check:
	composer check

security:
	composer security

security-dast:
	composer security:dast

lint:
	composer lint

rector:
	composer rector

stan:
	composer stan

psalm:
	composer psalm

psalm-taint:
	composer psalm:taint

test:
	composer test
