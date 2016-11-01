#!/bin/bash

bower install
composer install
php app/console assets:install --symlink web
php app/console assetic:dump
php app/console server:run &
firefox http://localhost:8000/rate/new &