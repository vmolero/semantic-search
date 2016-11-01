bower install
composer install
php app/console assets:install --symlink web
php app/console assetic:dump
