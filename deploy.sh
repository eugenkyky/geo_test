#!/usr/bin/env bash
#PHP install
sudo apt-get update &&
sudo apt-get install python-software-properties -y &&
sudo LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php -y &&
sudo apt-get update &&
sudo apt-get install php7.0 curl git libpcre3 zip unzip php7.0-xml php-mbstring php7.0-sqlite -y &&
(curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer) &&
git clone https://github.com/eugenkyky/geo_test.git test_assignment &&
#composer get additional packages
cd test_assignment &&
touch database/test.sqlite &&
chmod 777 database &&
chmod 777 database/test.sqlite &&
chmod 777 storage/logs &&
composer install &&
#create schema
php artisan migrate &&
#create users dir
echo "Deploy task succeded" &&
exit 0