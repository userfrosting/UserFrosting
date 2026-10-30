# UserFrosting Docker Environment

> You can find the complete version of thi guide at [UserFrosting Learn](https://learn.userfrosting.com/installation/environment/docker).

First, install [Docker Compose v2.20+ or Docker Desktop](https://docs.docker.com/compose/install/).

The Skeleton package supports development and production Docker configurations.

## Development

Initialize a new UserFrosting project:

1. Get UserFrosting repository : 
   ```bash
   docker run --rm -it -v "$(pwd):/app" composer create-project userfrosting/userfrosting UserFrosting "^6.0" --no-scripts --no-install --ignore-platform-reqs --stability=beta
   ```
2. Change to the new directory : 
   ```bash
   cd UserFrosting
   ```
3. Copy the default env file : 
   ```bash
   cp docker/.env.example .env
   cp app/.env.docker app/.env
   ```
   > [!NOTE]
   > The project-root `.env` selects the Compose file with `DOCKER_MODE` and sets `COMPOSE_PROJECT_NAME`, which defines the containers, images, networks, and volumes names.
   >
   > Compose passes the database settings from this file's `MYSQL_*` variables to the application, so those values stay synchronized.
   >
   > The default host ports are `8080` (HTTP), `5173` (Vite), `8503` (MySQL), `1025` (SMTP), and `8025` (Mailpit). Override them in the project-root `.env` when running multiple checkouts.
   >
   > Use this to run parallel instances of UserFrosting with isolated resources.
4. Build all the Docker containers:
   ```bash
   docker compose build --no-cache
   ```
5. Start all the containers:
   ```bash
   docker compose up -d
   ```
   > [!NOTE]
   > The Vue frontend installs dependencies from the current package files when its container starts. Its `node_modules` directory is stored separately from the host's, so changes to the lockfile are picked up on the next container start.
6. Set some directory permissions (your may have to enter your root password):
   ```bash
   sudo chown -R $USER: .
   sudo chmod 777 app/{logs,cache,sessions}
   ```
7. Install all composer modules used in UserFrosting:
   ```bash
   docker compose exec app composer update
   ```
8. Install UserFrosting (database configuration and migrations, creation of admin user, ...). You'll need to provide info to create the admin user.
   ```bash
   docker compose exec app php bakery bake
   ```
Now visit [http://localhost:8080](http://localhost:8080) to see your UserFrosting homepage!

> All calls to Bakery commands need to be prefixed by `docker compose`, since they need to run on the containers, not your computer. For example:
> `docker compose exec app php bakery ...`.

**You can paste these into a bash file and execute it!**

```bash
docker run --rm -it -v "$(pwd):/app" composer create-project userfrosting/userfrosting UserFrosting "^6.0" --no-scripts --no-install --ignore-platform-reqs  --stability=beta
cd UserFrosting
cp app/.env.docker app/.env
cp docker/.env.example .env
docker compose build --no-cache
docker compose up -d
sudo chown -R $USER: .
sudo chmod 777 app/{logs,cache,sessions}
docker compose exec app composer update
docker compose exec app php bakery bake
```

**Start / stop containers**

If you need to stop the UserFrosting docker containers, just change to your userfrosting directory and run:

```bash
docker compose stop
```

To start containers again, change to your userfrosting directory and run:

```bash
docker compose up -d
```

**Reset the database and containers**

To stop the containers but keep the database volume, run:

```bash
docker compose down --remove-orphans
```

To also delete the persisted database and start with a clean database, add `--volumes`:

```bash
docker compose down --remove-orphans --volumes
```

This permanently removes the database data and Docker-managed application volumes. In development, the frontend reinstalls its dependencies when it starts again. Source files and sprinkles are not deleted.

## Production

1. Copy the production app environment template:
   ```bash
   cp app/.env.docker.production app/.env
   ```
2. Set `DOCKER_MODE=production` in the project-root `.env`. Compose then selects `compose.production.yaml`.
3. Replace both sample MySQL passwords in the project-root `.env`, set a unique `CSRF_SECRET`, the public HTTPS `URI_PUBLIC`, and real SMTP settings in `app/.env`. Compose injects the MySQL credentials from the project-root `.env` into the PHP container, so do not duplicate them in the app env file.
4. Build and start the production images:
   ```bash
   docker compose build
   docker compose up -d
   ```
5. Apply database migrations and create the initial administrator:
   ```bash
   docker compose exec app php bakery migrate --force
   docker compose exec app php bakery create:admin-user
   ```

Production builds frontend assets into the image, installs Composer dependencies without development packages, and does not mount source code. The PHP image uses the Memcached extension and the application uses the private Memcached service for cache data. MySQL and Memcached are private to the Compose network; Mailpit, Vite, and development ports are not started or exposed. The database and application writable directories use Docker-managed volumes. Memcached is an ephemeral cache and is not persisted.

Do not run `php bakery bake` in the production container: that command also tries to build frontend assets, which are compiled into the image and do not require Node.js at runtime.

The web container listens on loopback at port 8080 by default. Put it behind a TLS-terminating reverse proxy; if that proxy is in another container or host, configure the production `ports` binding in `compose.production.yaml` for the proxy's network. Configure the proxy to forward the original HTTPS scheme and host. Back up the database volume and protect both environment files; the sample credentials and secrets must be replaced before deployment.
