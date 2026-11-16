# UserFrosting Docker Environment

> [!NOTE]
> You can find the complete version of this guide at [UserFrosting Learn](https://learn.userfrosting.com/installation/environment/docker).

The Skeleton package supports development and production Docker configurations.

## Development Environment

### Prerequisites

Install [Docker Compose v2.20+ or Docker Desktop](https://docs.docker.com/compose/install/).

### Initial Setup

Initialize a new UserFrosting project:

```bash
docker run --rm -it -v "$(pwd):/app" composer create-project userfrosting/userfrosting UserFrosting "^6.1" --no-scripts --no-install --ignore-platform-reqs
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

Display the Docker user and group IDs for your host account:

```bash
echo "DOCKER_UID=$(id -u)"
echo "DOCKER_GID=$(id -g)"
```

Copy the two displayed lines into `.env`. This lets the app run as `www-data` and the Vite frontend as `node` while keeping files created in bind mounts and the shared `node_modules` volume accessible to your host account. If the checkout or runtime directories are already owned by another user, fix their ownership before starting the containers.

> [!TIP]
> You can use the root `.env` file (copied from `docker/.env.example`) to define `COMPOSE_PROJECT_NAME`, which determines the names of containers, images, networks, and volumes. If it is unset, Compose uses the checkout directory name instead. You can set it to a unique value to avoid conflicts with other projects or branch checkouts.
>
> You can also define other environment variables here, such as database credentials or service ports. See [Advanced configuration](#advanced-configuration) for details.

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

### Running tests

To run tests in the app container with the testing environment:

```bash
docker compose exec app vendor/bin/phpunit
```

PHPUnit uses SQLite in memory by default. See [Advanced configuration](#advanced-configuration) to test against MySQL, MariaDB, or PostgreSQL.

## Advanced configuration

The default stack starts MySQL, Mailpit, the PHP app, nginx, and the Vue frontend. The optional project-root `.env` can enable additional services. Start by copying the example environment file to the project root:

```bash
cp docker/.env.example .env
```

After changing `.env`, apply the settings with `docker compose up -d`. An existing container does not receive changed environment variables until Compose recreates it. When disabling profiles, stop the old services explicitly or use `docker compose down --remove-orphans` before restarting. Avoid removing volumes unless you intend to delete data.

### Selecting the PHP version

Set `DOCKER_PHP_VERSION` in the project-root `.env` to `8.3`, `8.4`, or `8.5`. It defaults to `8.5` and applies to both development and production images. Rebuild the app image after changing the value:

```dotenv
DOCKER_PHP_VERSION=8.4
```

```bash
docker compose up -d --build app
```

### Docker structure

The root [`compose.yaml`](../compose.yaml) defines the core services and includes the individual service files in the `docker/` directory:

| Service                                    | Purpose                                      | Default           |
| ------------------------------------------ | -------------------------------------------- | ----------------- |
| `app`                                      | PHP application and Bakery commands          | Started           |
| `nginx`                                    | Web server for the application               | Started           |
| `vue-frontend`                             | Vite development server                      | Started           |
| `mysql`                                    | Application database                         | Started           |
| `mailpit`                                  | Captures outgoing email                      | Started           |
| `mariadb`, `pgsql`                         | Alternative application databases            | Optional profiles |
| `mysql-test`, `mariadb-test`, `pgsql-test` | Isolated, ephemeral test databases           | Optional profiles |
| `memcached`, `redis`                       | Cache services for development and debugging | Optional profiles |

### Container Names

`COMPOSE_PROJECT_NAME` can be set to customize the names of containers, images, networks, and volumes.

### Service Profiles

Set `COMPOSE_PROFILES` to a comma-separated list of the following profiles:

- `mariadb`
- `pgsql`
- `mysql-test`
- `mariadb-test`
- `pgsql-test`
- `memcached`
- `redis`

Multiple profiles can be enabled together. Profiles start services; they do **not** change the app's database or cache configuration. The appropriate variable in the `.env` file must be set to select which service the app uses as defined below.

### Application databases

MySQL is always started, even if another provider is selected. To use MariaDB or PostgreSQL instead, enable its profile and select it for the app. For example, set these values in the root `.env`:

```dotenv
COMPOSE_PROFILES=pgsql
DOCKER_DB_CONNECTION=pgsql
```

For MariaDB use `mariadb` in both settings. Set `DOCKER_DB_CONNECTION=mysql` (or delete the variable) to return to the default. More than one provider may be running at the same time.

MySQL, MariaDB, and PostgreSQL use separate persistent volumes. Run `docker compose exec app php bakery bake` when setting up a new application database.

`DOCKER_DB_NAME`, `DOCKER_DB_USER`, and `DOCKER_DB_PASSWORD` configure the app database and are shared by all three providers. `DOCKER_DB_ROOT_PASSWORD` sets the MySQL and MariaDB administrator password. Default values will be used if these variables are not set. The database images apply these initialization settings only to an empty data directory. Changing credentials or database names in `.env` does not update an existing volume; update the database itself or deliberately provision a new volume after backing up its data.

The database ports are exposed on the host machine, allowing external connections if needed. These can be customized in the `.env` file. Check `docker/.env.example` for the default ports.

### Provider-backed test databases

By default, PHPUnit's tests use in-memory SQLite and require no specific service to run. For a server-backed test (e.g., MySQL, MariaDB, or PostgreSQL), enable the matching test profile and select the provider separately.

For example, to run tests with MySQL:

```dotenv
COMPOSE_PROFILES=mysql-test
DOCKER_DB_TEST_CONNECTION=mysql
```

For MariaDB:

```dotenv
COMPOSE_PROFILES=mariadb-test
DOCKER_DB_TEST_CONNECTION=mariadb
```

For PostgreSQL:

```dotenv
COMPOSE_PROFILES=pgsql-test
DOCKER_DB_TEST_CONNECTION=pgsql
```

> [!NOTE]
> The application and test database providers may differ.

As with the application database, the test database credentials can be customized with the optional `DOCKER_DB_TEST_NAME`, `DOCKER_DB_TEST_USER`, and `DOCKER_DB_TEST_PASSWORD` variables.

> [!WARNING]
> Server-backed tests may reset the configured test database. Never point them at an application database, and do not run concurrent suites against the same test database. Test data is stored in memory and is lost when its container stops or is recreated. These services are for testing, not persistent application data.

### Selecting the cache provider

Memcached and Redis are optional, independent services. Enable the service profile **and** set `DOCKER_CACHE_DRIVER` to use it from the app. For example:

```dotenv
COMPOSE_PROFILES=memcached
DOCKER_CACHE_DRIVER=memcached
```

Or:

```dotenv
COMPOSE_PROFILES=redis
DOCKER_CACHE_DRIVER=redis
```

Both cache services may be started for debugging without selecting either as the cache driver. If available, PHPUnit will use them. These tests write to and flush Memcached and Redis, which may affect existing cache data.

The app connects to `memcached` or `redis` over the backend network automatically; neither service publishes a host port. Cache data is not intended as durable storage and may be lost when the container stops. The testing configuration uses the array cache regardless of the application cache driver.

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
> This permanently removes the Compose project's application database volumes and frontend dependency volume. Test database data is ephemeral and disappears when its container stops. Volumes from other projects and the legacy MySQL volume may remain. Source files and sprinkles are not deleted.

## Production Environment

The production environment uses a separate Docker Compose configuration and environment settings optimized for deployment. Compose explicitly sets `UF_MODE=production` and `VITE_DEV_ENABLED=false`, so it always serves the compiled assets rather than trying to connect to the Vite development server. The production image build installs npm dependencies with `npm ci` when a lockfile is present, or `npm install` otherwise, then compiles the frontend with `npm run vite:build`. It installs Composer dependencies without development packages. The compiled public assets and Vite manifest are copied into the web and PHP images respectively; source code is not mounted at runtime. Before PHP-FPM starts, the app waits for the selected cache service to be healthy and runs Bakery's `clear-cache` command. Mailpit is not available in the production environment, **you'll need a real SMTP server for sending emails**.

Production defaults to MySQL and Memcached cache driver. The `mariadb` and `pgsql` profiles provide alternative databases; the `redis` profile provides an alternative cache. Select the profile and matching driver with `COMPOSE_PROFILES`, `DOCKER_DB_CONNECTION`, `DOCKER_DB_PORT`, and `DOCKER_CACHE_DRIVER` in the project-root `.env`. For example:

```dotenv
COMPOSE_PROFILES=pgsql,redis
DOCKER_DB_CONNECTION=pgsql
DOCKER_DB_PORT=5432
DOCKER_DB_NAME=userfrosting
DOCKER_DB_USER=userfrosting
DOCKER_DB_PASSWORD=replace-with-a-secret
DOCKER_CACHE_DRIVER=redis
```

Production database-test profiles are not provided in production.

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

Set production mode and replace both database passwords in the project-root `.env`:
```env
DOCKER_MODE=production
DOCKER_DB_ROOT_PASSWORD=replace-with-a-unique-root-secret
DOCKER_DB_PASSWORD=replace-with-a-unique-app-secret
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
