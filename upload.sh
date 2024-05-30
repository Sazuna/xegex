#!/bin/bash

if [ ! -d /var/www/html/xegex/ ]; then
 sudo mkdir /var/www/html/xegex/
fi
sudo cp -r * /var/www/html/xegex/

echo "localhost/xegex/projets_manager.php"
date
