# UserFrosting Docker Environment

> You can find the complete version of this guide at [UserFrosting Learn](https://learn.userfrosting.com/installation/environment/docker).

The Skeleton package supports development and production Docker configurations.

## Development Environment

### Prerequisites

Install [Docker Compose v2.20+ or Docker Desktop](https://docs.docker.com/compose/install/).

### Initial Setup

Initialize a new UserFrosting project:

```bash
docker run --rm -it -v "$(pwd):/app" composer create-project userfrosting/userfrosting UserFrosting "^6.1" --no-scripts --no-install --ignore-platform-reqs --stability=beta
```

Change to the new directory:

```bash
cd UserFrosting
```

Copy the default environment files:

```bash
cp docker/.env.example .env
cp app/.env.docker app/.env
```

> [!TIP]
> The `.env` file (copied from `docker/.env.example`) can define `COMPOSE_PROJECT_NAME`, which determines the names of containers, images, networks, and volumes. If it is unset, Compose uses the checkout directory name instead. You can set it to a unique value to avoid conflicts with other projects or branch checkouts.
>
> You can also define other environment variables here, such as database credentials or service ports.

> [!NOTE]
> Compose passes the database settings from `MYSQL_*` environment variables in this file to the PHP app, keeping its connection credentials synchronized with MySQL. You don't need to configure the database connection manually in the app, including in its `.env` file.

Set the directory permissions (you may need to enter your root password):

```bash
sudo chown -R $USER: .
sudo chmod 777 app/{logs,cache,sessions}
```

### Build and Start Containers

Build and start all the Docker containers:

```bash
docker compose up -d --build
```

> [!NOTE]
> At this point, the Docker containers are built and running, but setup is not yet complete. You still need to install the Composer dependencies and run the Bakery commands to set up UserFrosting. Otherwise, the UI will not function correctly and may display a blank page.

### Install Composer Modules

Install all Composer dependencies used by UserFrosting:

```bash
docker compose exec app composer update
```

### Install UserFrosting

Set up UserFrosting by running the Bake command to apply database migrations and create an administrator account. You'll be prompted for the information needed to create the account.

```bash
docker compose exec app php bakery bake
```

Now visit [http://localhost:8080](http://localhost:8080) to see your UserFrosting homepage!

## Using the Docker Environment

### Start/stop containers

To stop the UserFrosting Docker containers, change to your UserFrosting directory and run:

```bash
docker compose stop
```

To start the containers again, change to your UserFrosting directory and run:

```bash
docker compose up -d
```

If you need to rebuild the containers without starting them, run:

```bash
docker compose build
```

### Running Bakery commands

Prefix all Bakery commands with `docker compose` so they run in the containers rather than on your computer. For example:

```bash
docker compose exec app php bakery ...
```

### Updating frontend dependencies

The `app` and `vue-frontend` containers share a Docker-managed `node_modules` volume, separate from your host's `node_modules`. The Vue frontend waits for the dependencies to be installed by Bakery before starting Vite. On a fresh setup, `docker compose exec app php bakery bake` installs the locked dependencies as part of setup.

To install dependencies from the current lockfile, run:

```bash
docker compose exec app php bakery assets:install
```

To update dependencies and the lockfile, run:

```bash
docker compose exec app php bakery assets:update
```

After changing dependencies, restart the Vue dev server to ensure it reloads them:

```bash
docker compose restart vue-frontend
```

> [!NOTE]
> Vue Hot Module Replacement (HMR) is enabled and will automatically reload the frontend when changes are detected in the source files. You don't need to restart the Vue dev server manually when editing source files.

### Using Mailpit

Mailpit is used to capture outgoing emails from the application. You can access the Mailpit web interface at [http://localhost:8025](http://localhost:8025) to view and manage emails sent by UserFrosting.

### Reset the database and containers

To remove the containers while keeping the database and frontend dependency volumes, run:

```bash
docker compose down --remove-orphans
```

To also delete the persisted database and frontend dependency volumes and start with a clean slate, add `--volumes`:

```bash
docker compose down --remove-orphans --volumes
```

> [!WARNING]
> This permanently removes the database data volume. Source files and sprinkles are not deleted.

## Production Environment

The production environment uses a separate Docker Compose configuration and environment settings optimized for deployment. The production image build installs npm dependencies with `npm ci` when a lockfile is present, or `npm install` otherwise, then compiles the frontend with `npm run vite:build`. It installs Composer dependencies without development packages. The compiled public assets and Vite manifest are copied into the web and PHP images respectively; source code is not mounted at runtime. Before PHP-FPM starts, the app waits for Memcached to be healthy and runs Bakery's `clear-cache` command against the production Memcached cache and application cache files. Memcached is used for runtime caching. Mailpit is not available in the production environment, so you'll need a real SMTP server for sending emails.

To set up and run the production environment, first make sure your project is configured and tested on the Docker [development environment](#development-environment). To serve the production environment on a live server, you'll need to install Docker and Docker Compose on the server, and copy or pull your project files to the server.

### Setting Up the Production Environment

Copy the production app environment template:
```bash
cp app/.env.docker.production app/.env
```

Edit the root Docker `.env` file:
```bash
nano .env
```

Set `DOCKER_MODE=production` to enable the production environment and replace **both** sample MySQL passwords:
```diff
- DOCKER_MODE=development
+ DOCKER_MODE=production

MYSQL_ROOT_PASSWORD=replace-this-before-production <-- Change this !
MYSQL_PASSWORD=replace-this-before-production <-- Change this !
```

Next, edit the project `app/.env` file:
```bash
nano app/.env
```

Set a unique `CSRF_SECRET`, the public HTTPS `URI_PUBLIC`, and real SMTP settings in `app/.env`.

Docker will inject the MySQL credentials from the project-root `.env` file into the PHP container, so you don't need to duplicate them in the app's environment file.

### Build and Start Containers

Build and start the production images:
```bash
docker compose up -d --build
```

Apply database migrations:
```bash
docker compose exec app php bakery migrate --force
```

If this is the first time setting up the production environment, you will also need to create the initial administrator user:
```bash
docker compose exec app php bakery create:admin-user
```

> [!WARNING]
> Avoid running `php bakery bake` in the production container: that command also tries to build frontend assets, which are compiled into the image and do not require Node.js at runtime. Prefer running individual Bakery commands like `php bakery migrate` and `php bakery create:admin-user` instead.

### Setting Up HTTPS

The web container listens on loopback at port 8080 by default. You can configure a different port by modifying the `DOCKER_HTTP_PORT` variable in the root `.env` file. This is useful if you run multiple containers on the same host and need to avoid port conflicts.

To enable HTTPS, configure a TLS-terminating reverse proxy in front of the web container. The reverse proxy handles the HTTPS connection and forwards requests to the web container over HTTP:

```text
Browser <-----> Reverse Proxy (HTTPS) <-----> Web Container (HTTP)
```

Ensure that your reverse proxy is configured to handle TLS termination and forward requests to the web container over HTTP. Nginx and Traefik are common choices for managing HTTPS in containerized environments.

### Updating the Production Environment

When updating the production environment, stop the running containers, fetch the latest changes, and rebuild the production image with `docker compose up -d --build`. The Composer and npm dependencies will be updated as part of the build process and frontend assets will be recompiled. When the app container starts, Bakery's `clear-cache` command clears the configured Memcached cache and application cache files.

Avoid running `php bakery bake` as it attempts to rebuild frontend assets. Instead, use individual Bakery commands like `php bakery migrate` for database migrations, or other purposes.
