#!/bin/bash
 sudo -k
 echo "Stoppe nginx, mariadb und redis..."
 sudo bash -c "
        service nginx stop
        service mariadb stop
        service redis stop
"

echo "Starte Laravel Sail..."
./vendor/bin/sail up
