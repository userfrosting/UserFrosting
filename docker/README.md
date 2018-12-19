# Docker Development Environment

First, install [Docker Compose](https://docs.docker.com/compose/install/).

Second, initialize a new UserFrosting project:
1. Clone the repository `git clone https://github.com/userfrosting/UserFrosting.git .` and change into that directory `cd userfrosting`
1. Run `cp app/sprinkles.example.json app/sprinkles.json` or upload your own (also upload your sprinkles if you have some)
2. Run `sudo chown -R 33 app/{logs,cache,sessions}` (Changes the user to the www-data user of the image, more information [here](https://serversforhackers.com/c/dckr-file-permissions) )
2. Run `sudo docker-compose run composer install` to install all composer modules.
3. Run `sudo docker-compose run node npm install` to install all npm modules.

Now you can start up the entire Nginx + PHP + MySQL stack using docker with:

    $ sudo docker-compose up -d

On the first run you need to init the database (Be sure to execute this in the same directory, `${PWD##*/}` is a statement to get your current working directorys name. Docker uses it to name your container):

    $ sudo docker exec -it -u www-data ${PWD##*/}_php_1 bash -c 'php bakery migrate'
    
You also need to setup the first admin user (again, `${PWD##*/}` is a statement to get your current working directorys name):

    $ sudo docker exec -it -u www-data ${PWD##*/}_php_1 bash -c 'php bakery create-admin'

Now visit http://localhost:8570/ to see your UserFrosting homepage!

**This is not (yet) meant for production!!**

You may be tempted to run with this in production but this setup has not been security-hardened. For example:

- Database is exposed on port 8571 so you can access MySQL using your favorite client at localhost:8571. However,
  the way Docker exposes this actually bypasses common firewalls like `ufw` so this should not be exposed in production.
- Database credentials are hard-coded so obviously not secure.
- File permissions may be more open than necessary.
- It just hasn't been thoroughly tested in the capacity of being a production system.

## Updating your code
As you might guessed you will have to run 

    $ sudo docker exec -it -u www-data userfrosting_php_1 bash -c 'php bakery migrate'
    
again if you want to migrate tables.
You can change `php bakery migrate` to other `bakery` commands as well.
Be aware that the userfrosting container doesn't know about npm!  
Similary for composer:

    $ sudo docker-compose run composer update
   
See the [Docker](https://docs.docker.com/engine) and [Docker-compose documentation](https://docs.docker.com/compose/) for more details.
