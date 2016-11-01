call bower install
call composer install
call php app/console assets:install --symlink web
call php app/console assetic:dump
start php app/console server:run
start firefox http://localhost:8000/rate/new