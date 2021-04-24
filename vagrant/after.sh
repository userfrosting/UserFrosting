#!/bin/sh

BASE_PATH="/home/vagrant/userfrosting"

# Welcome message
echo "\n\n"
echo " *****************************\n"
echo " * Welcome to UserFrosting ! *\n"
echo " *****************************\n"

# Update nodejs
echo "\n\n >> Updating npm\n"
npm cache clean -f
npm install -g n
n -q lts

# Ensure composer deps are installed
echo "\n\n >> Installating Composer\n"
cd ${BASE_PATH}
composer self-update --1
composer install

# Setup .env
echo "\n\n >> Setting up Sprinkles\n"
echo 'UF_MODE=""' > app/.env
echo 'DB_DRIVER="mysql"' >> app/.env
echo 'DB_HOST="localhost"' >> app/.env
echo 'DB_PORT="3306"' >> app/.env
echo 'DB_NAME="homestead"' >> app/.env
echo 'DB_USER="homestead"' >> app/.env
echo 'DB_PASSWORD="secret"' >> app/.env
echo 'SMTP_HOST="host.example.com"' >> app/.env
echo 'SMTP_USER="relay@example.com"' >> app/.env
echo 'SMTP_PASSWORD="password"' >> app/.env

# Setup sprinkles.json
cp app/sprinkles.example.json app/sprinkles.json

# Install UserFrosting
echo "\n\n >> UserFrosting installation\n"
php bakery debug
php bakery migrate
php bakery create-admin --username="admin" --email="admin@userfrosting.test" --password="adminadmin12" --firstName="Admin" --lastName="istrator"
php bakery build-assets

echo "\n\nUserFrosting should be ready at http://userfrosting.test (Don't forget to update your hosts file !)"
