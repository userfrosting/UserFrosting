#!/bin/sh

BASE_PATH="/home/vagrant/userfrosting"

# Update nodejs
npm cache clean -f
npm install -g n
n -q lts

# Ensure composer deps are installed
cd ${BASE_PATH}
composer install

# Setup .env
echo 'UF_MODE=""' > app/.env
echo 'DB_DRIVER="mysql"' >> app/.env
echo 'DB_HOST="localhost"' >> app/.env
echo 'DB_PORT="3306"' >> app/.env
echo 'DB_NAME="UserFrosting"' >> app/.env
echo 'DB_USER="homestead"' >> app/.env
echo 'DB_PASSWORD="secret"' >> app/.env
echo 'SMTP_HOST="host.example.com"' >> app/.env
echo 'SMTP_USER="relay@example.com"' >> app/.env
echo 'SMTP_PASSWORD="password"' >> app/.env

# Setup sprinkles.json
cp app/sprinkles.example.json app/sprinkles.json

# Install UserFrosting
php bakery debug
php bakery migrate
php bakery create-admin --username="admin" --email="admin@userfrosting.test" --password="adminadmin12" --firstName="Admin" --lastName="istrator"
php bakery build-assets

echo "\n\nUserFrosting is ready at http://192.168.10.10/"
