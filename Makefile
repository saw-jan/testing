SHELL := /bin/bash

COMPOSER_BIN := $(shell command -v composer 2> /dev/null)
ifndef COMPOSER_BIN
    $(error composer is not available on your system, please install composer)
endif

# directories
app_name=$(notdir $(CURDIR))
build_dir=$(CURDIR)/build
dist_dir=$(build_dir)/dist
src_files=README.md LICENSE
src_dirs=appinfo data img lib locking
all_src=$(src_dirs) $(src_files)

# bin file definitions
PHPLINT=php -d zend.enable_gc=0  vendor-bin/php-parallel-lint/vendor/bin/parallel-lint

# start with displaying help
.DEFAULT_GOAL := help

help:
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'



##
## Build targets
##--------------------------------------

.PHONY: dist
dist:                       ## Build distribution
dist: distdir package

.PHONY: distdir
distdir:
	rm -rf $(build_dir)
	mkdir -p $(dist_dir)/$(app_name)
	cp -R $(all_src) $(dist_dir)/$(app_name)

.PHONY: package
package:
	tar -czf $(dist_dir)/$(app_name).tar.gz -C $(dist_dir) $(app_name)

##
## Tests
##--------------------------------------

.PHONY: test-php-lint
test-php-lint:             ## Run phan
test-php-lint: vendor-bin/php-parallel-lint/vendor
	$(PHPLINT) appinfo lib locking

#
# Dependency management
#--------------------------------------

composer.lock: composer.json
	@echo composer.lock is not up to date.

vendor:
	composer install --no-dev

vendor/bamarni/composer-bin-plugin:
	composer install

vendor-bin/php-parallel-lint/vendor: vendor/bamarni/composer-bin-plugin vendor-bin/php-parallel-lint/composer.lock
	composer bin php-parallel-lint install --no-progress

vendor-bin/php-parallel-lint/composer.lock: vendor-bin/php-parallel-lint/composer.json
	@echo php-parallel-lint composer.lock is not up to date.