call bower install
call composer install
call php app/console assets:install --symlink web
call php app/console assetic:dump