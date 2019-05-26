# Docker Development Environment

>Docker support is currently at **experimental** stability. Expect quirks and documentation gaps.<br/>
>This is also documented at [UserFrosting Learn](https://learn.userfrosting.com/installation/environment/docker).

**This is not (yet) meant for production!**

You may be tempted to run with this in production but this setup has not been security-hardened. For example:

- Database is exposed on port 8593 so you can access MySQL using your favorite client at localhost:8593. However,
  the way Docker exposes this actually bypasses common firewalls like `ufw` so this should not be exposed in production.
- Database credentials are hard-coded so obviously not secure.
- File permissions may be more open than necessary.
- HTTPS not implemented fully
- It just hasn't been thoroughly tested in the capacity of being a production system.


## Environment Dependencies

Though Docker will take care of this for the most part, there are 2 things you'll need to get started.

* [Docker Compose](https://docs.docker.com/compose/install/)
* Bash plus the common Linux tools (for Windows users, Git Bash will cover this)

## First Run

>Certain `bakery` commands such as `bake` that use NodeJS are not currently supported but should work provided a suitable image is used.

Run the following through `bash` from the project root directory;

```bash
# Create ".env" and "sprinkles.json" from examples
cp app/sprinkles.example.json app/sprinkles.json
cp app/.env.example app/.env # This will need to be updated to reflect docker-compose.yml

# Install composer dependencies
docker run --rm -it -v `pwd -W || pwd`:/app composer update  --ignore-platform-reqs --no-scripts
# Alternative (deprecated): docker-compose run composer install --ignore-platform-reqs --no-scripts

# Install npm dependencies
docker run --rm -it -v `pwd -W || pwd`:/app node:alpine sh -c "cd /app/build ; npm install ; npm run uf-assets-install"
# Alternative (deprecated): docker-compose run node  sh -c "cd /app/build ; npm install ; npm run uf-assets-install"

# Prepare folder permissions
chmod 777 app/{logs,cache,sessions}

# Bring up the stack
docker-compose up -d

# Capture container name
docker_container=$(docker ps -q --filter="ancestor=userfrosting_php")

# Perform database migration
docker exec -it -u www-data $docker_container sh -c "php bakery migrate"

# Create admin account
docker exec -it -u www-data $docker_container sh -c "php bakery create-admin"
```

Now visit `http://localhost:8591/` to see your UserFrosting homepage!
