#!/bin/bash

rm -rf build
mkdir -p build
php -dphar.readonly=0 vendor/bin/pharynx -f vendor -f virion.yml -s src
mv output.phar build/DatabaseExecutor.phar
php -dphar.readonly=0 vendor/bin/pharynx -i example
mv output.phar build/ExamplePlugin.phar