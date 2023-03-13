# Air Transport made with Pimcore

Demo application for booking transport by filling in the form and automatically sending e-mails.

Features:
* Backend build form
* All fields validation
* Dynamic "cargoes" field
* Saving transport information into pimcore DataObjects
* Automatic Email sending based on chosen airplane

## Instalation with Docker

You don't need to have a PHP environment with composer installed.

### Prerequisits

* Your user must be allowed to run docker commands (directly or via sudo).
* You must have docker-compose installed.
* Your user must be allowed to change file permissions (directly or via sudo).

### Follow these steps
1. Part of the project is a docker compose file
    * Run `` echo `id -u`:`id -g` `` to retrieve your local user and group id
    * Open the `docker-compose.yml` file in an editor, find `user: '1000:1000'` lines and update the ids if necessary
    * Start the needed services with `docker-compose up -d`

2. Install dependencies `docker-compose exec php composer install`

3. Create `.env.local` file in root directory and set dsn variable to send emails over smtp `MAILER_DSN=`
    * Example with mailtrap: `MAILER_DSN=smtp://user:pass@sandbox.smtp.mailtrap.io:2525?encryption=tls&auth_mode=login`

4. Restart containers `docker-compose restart`

5. Install pimcore and initialize the DB
    `docker-compose exec php vendor/bin/pimcore-install --mysql-host-socket=db --mysql-username=pimcore --mysql-password=pimcore --mysql-database=pimcore`
    * When asked for admin user and password: Choose freely
    * This can take a while, up to 20 minutes

6. Create necessary data with `docker-compose exec php php bin/console transport:create-data`

7. DONE - You can now visit the project:
    * The frontend: <http://localhost/form>
    * The admin interface, using the credentials you have chosen above:
      <http://localhost/admin>
