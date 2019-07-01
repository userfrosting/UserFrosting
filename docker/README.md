# Docker Development Environment

>This is also documented at [UserFrosting Learn](https://learn.userfrosting.com/installation/environment/docker).

First, install [Docker Compose](https://docs.docker.com/compose/install/).

Second, initialize a new UserFrosting project:

1. Copy `app/sprinkles.example.json` to `app/sprinkles.json`
2. Run `chmod 777 app/{logs,cache,sessions}` to fix file permissions for web server. (NOTE: File
   permissions should be properly secured in a production environment!)
3. Run `docker-compose run composer install --ignore-platform-reqs --no-scripts` to install all composer modules. (https://hub.docker.com/_/composer) Sometimes dependencies or Composer scripts require the availability of certain PHP extensions. You can work around this as follows: Pass the `--ignore-platform-reqs and --no-scripts` flags to install or update
4. Run `docker-compose run node npm install` to install all npm modules.
5. Run `docker-compose run composer update --ignore-platform-reqs --no-scripts` to install remaining composer modules
6. Run `docker-compose run node npm run uf-assets-install` to install all frontend vendor assets.

Now you can start up the entire Nginx + PHP + MySQL stack using docker with:

    $ docker-compose up -d

the `-d` flag will launch this in the background so you can continue to use the terminal window. On the first run you need to init the database (your container name may be different depending on the name of your root directory):

    $ docker exec -it -u www-data userfrosting_php_1 sh -c 'php bakery migrate'

You also need to setup the first admin user (again, your container name may be different depending on the name of your root directory):

    $ docker exec -it -u www-data userfrosting_php_1 sh -c 'php bakery create-admin'

Now visit `http://localhost:8591/` to see your UserFrosting homepage!

**Paste these into a bash file and execute it!**

```
chmod 777 app/{logs,cache,sessions}
docker-compose build --force-rm --no-cache
docker-compose run composer install --ignore-platform-reqs --no-scripts
docker-compose run node npm install
docker-compose run composer update --ignore-platform-reqs --no-scripts
docker-compose run node npm run uf-assets-install
docker-compose up -d
echo -n "Enter Docker Container Name --> "
read docker_container
docker exec -it -u www-data $docker_container sh -c 'php bakery migrate'
docker exec -it -u www-data $docker_container sh -c 'php bakery create-admin'
```

**This is not (yet) meant for production!**

You may be tempted to run with this in production but this setup has not been security-hardened. For example:

- Database is exposed on port 8593 so you can access MySQL using your favorite client at localhost:8593. However,
  the way Docker exposes this actually bypasses common firewalls like `ufw` so this should not be exposed in production.
- Database credentials are hard-coded so obviously not secure.
- File permissions may be more open than necessary.
- HTTPS not implemented fully
- It just hasn't been thoroughly tested in the capacity of being a production system.
