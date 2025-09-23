# Docker Development Environment

> You can find the complete version of thi guide at [UserFrosting Learn](https://learn.userfrosting.com/installation/environment/docker).

First, install [Docker Compose or Docker Desktop](https://docs.docker.com/compose/install/).

Second, initialize a new UserFrosting project:

1. Get UserFrosting repository : 
   ```
   docker run --rm -it -v "$(pwd):/app" composer create-project userfrosting/userfrosting UserFrosting "^6.0" --no-scripts --no-install --ignore-platform-reqs
   ```
2. Change to the new directory : 
   ```
   cd UserFrosting
   ```
3. Copy the default env file : 
   ```
   cp app/.env.docker app/.env
   ```
4. Build all the docker containers:
   ```
   docker-compose build --no-cache
   ```
5. Start all the containers:
   ```
   docker-compose up -d
   ```
6. Set some directory permissions (your may have to enter your root password):
   ```
   sudo chown -R $USER: .
   sudo chmod 777 app/{logs,cache,sessions}
   ```
7. Install all composer modules used in UserFrosting:
   ```
   docker-compose exec app composer update
   ```
8. Install UserFrosting (database configuration and migrations, creation of admin user, ...). You'll need to provide info to create the admin user.
   ```
   docker-compose exec app php bakery bake
   ```

Now visit [http://localhost:8080](http://localhost:8080) to see your UserFrosting homepage!

> All call to bakery commands need to be prefixed by docker-compose, since you need to run theses commands on the containers, not your computer. For example : `docker-compose exec app php bakery ...`.

**You can paste these into a bash file and execute it!**

```bash
docker run --rm -it -v "$(pwd):/app" composer create-project userfrosting/userfrosting UserFrosting "^6.0" --no-scripts --no-install --ignore-platform-reqs
cd UserFrosting
cp app/.env.docker app/.env
docker-compose build --no-cache
docker-compose up -d
sudo chown -R $USER: .
sudo chmod 777 app/{logs,cache,sessions}
docker-compose exec app composer update
docker-compose exec app php bakery bake
```

**Start / stop containers**

If you need to stop the UserFrosting docker containers, just change to your userfrosting directory and run:

```
docker-compose stop
```

To start containers again, change to your userfrosting directory and run:

```
docker-compose up -d
```

**Purge docker containers to start over**

If you need to purge your docker containers (this will not delete any source file or sprinkle, but will empty the database), run:

```
docker-compose down --remove-orphans
```

And then start the installation process again.

**This is not (yet) meant for production!**

You may be tempted to run with this in production but this setup has not been security-hardened. For example:

- Database is exposed on port 8593 so you can access MySQL using your favorite client at localhost:8593. However, the way Docker exposes this actually bypasses common firewalls like `ufw` so this should not be exposed in production.
- Database credentials are hard-coded so obviously not secure.
- File permissions may be more open than necessary.
- HTTPS not implemented fully
- It just hasn't been thoroughly tested in the capacity of being a production system.
