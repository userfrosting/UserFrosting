# Using Docker with UserFrosting

>Docker support is currently at **experimental** stability, and is **not yet ready for production**. Expect quirks and documentation gaps, workarounds may be required in places.

>Refer to [UserFrosting Learn - Docker](https://learn.userfrosting.com/installation/environment/docker) for more comprehensive documentation.

UserFrosting includes support for Docker. This allows you to run UserFrosting without needing to set up your own local web server with traditonal WAMP/MAMP stacks. It also provides a consistent environment between developers for writing and debugging code changes more productively.

UserFrosting uses Docker Compose to orchastrate the infrustructure. It provides PHP 7.2, NGINX, and MySQL 5.7. To keep the footprint small, the `alpine` image variants are used where possible.

## Get Started

1. Download and Install [Docker Compose](https://docs.docker.com/compose/install/)

2. Clone your fork of UserFrosting (if not done already)

3. Create `sprinkles.json` and `.env` from examples
   ```bash
   cp app/sprinkles.example.json app/sprinkles.json
   cp app/.env.example app/.env
   ```

4. Install composer and npm dependencies
   ```bash
   docker run --rm -itv `pwd -W 2>/dev/null || pwd`:/app composer update  --ignore-platform-reqs
   docker run --rm -itv `pwd -W 2>/dev/null || pwd`:/app node:alpine sh -c "cd /app/build ; npm install ; npm run uf-assets-install"
   ```

5. Allow UserFrosting write access to necessary folders
   ```bash
   chmod 777 app/{logs,cache,sessions}
   ```

6. Start the services, this includes MySQL, NGINX, and PHP
   ```bash
   docker-compose up -d
   ```

7. Capture name of PHP service, this is needed to run commands against the active instance
   ```bash
   docker_container=$(docker ps -q --filter="ancestor=userfrosting_php")
   ```

8. Perform database migration
   ```bash
   docker exec -it -u www-data $docker_container sh -c "php bakery migrate"
   ```

9. Create admin account
   ```bash
   docker exec -it -u www-data $docker_container sh -c "php bakery create-admin"
   ```

10. Access UserFrosting at `http://localhost:8591/`

## Additional Commands

* Start all services
  >You may wish to run this without the detached (`-d`) flag for debugging purposes.<br/>
  >This command may be safely run multiple times to restart stopped services.
  ```bash
  docker-compose up -d
  ```

* Stop all services
  ```bash
  docker-compose stop
  ```

* Stop and remove all services
  ```bash
  docker-compose down
  ```
  >Removing all services will also remove database data.

* Run a bakery command
  ```bash
  docker_container=$(docker ps -q --filter="ancestor=userfrosting_php")
  docker exec -it -u www-data $docker_container sh -c "php bakery COMMAND_NAME"
  ```
  >Not all bakery commands are supported under the current Docker configuration, such as the `bake` quick start command. This will be addressed in a future release.

## Known Limitations

* Performance under Windows may be poor due to Docker using a Hyper-V virtual machine to run the Linux images our configuration depends on. Long term, this should be addressed by [Docker for Windows being overhauled to use WSL 2](https://engineering.docker.com/2019/06/docker-hearts-wsl-2/).
* The current Docker configuration is not suitable for production workloads. Another deployment strategy is required at this time.
* Database environment variables are overriden by values hard coded in the Docker configuration, and cannot be overriden in `app/.env`.

---

## MySQL Access

- Hostname: 127.0.0.1
- Username: docker
- Password: secret
- Database: userfrosting
- Port: 8593
