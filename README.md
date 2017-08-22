# Adventure Lookup

Repository of Adventure Lookup, proposed by [/u/mattcolville](https://www.reddit.com/user/mattcolville).

# Setting up a development environment

To get you up and running quickly, you should use [Vagrant](https://vagrantup.com) and [VirtualBox](https://virtualbox.org) to start up a VM with all the dependencies preinstalled.

After downloading and installing Vagrant and VirtualBox:
```
# Clone the repo to your local machine (this is readonly; you need to fork if you want write)
git clone git@github.com:AdventureLookup/AdventureLookup.git
 
cd AdventureLookup
 
# Create and provision the VM
vagrant up
 
# Log into the VM
vagrant ssh
```

Execute the following commands to finish the installation:
```
# Install PHP dependencies
composer install -n
 
# Install Frontend dependencies, can be run outside the virtual machine
npm install

# Setup database
php bin/console doctrine:migrations:migrate
 
# Create Elasticsearch index
php bin/console app:elasticsearch:reindex
 
# Import dummy adventures
php bin/console doctrine:fixtures:load --fixtures src/AppBundle/DataFixtures/ORM/RandomAdventureData.php
php bin/console app:elasticsearch:reindex
```

If you didn't use Vagrant and use an existing MySQL database, adjust the `app/config/parameters.yml` file to match your database credentials.

# Running the application

```
# Start Symfony development server on port 8000 to run the application
# Must be run inside the virtual machine you used `vagrant ssh` to get into earlier
php bin/console server:start 0.0.0.0:8000
 
# Start webpack to watch changes to assets and recompile them
# Can be run inside the virtual machine or outside of the virtual machine
# If run inside the virtual machine:
npm run dev-server-guest
# If run outside the virtual machine:
npm run dev-server-host
```

The application is now running at http://localhost:8000.
ElasticSearch can be accessed at http://localhost:9200.

## Running tests

Tests use PHPUnit to run. There are three testsuites, one with unit tests, one with functional tests 
and one with browser tests. 
Unit tests can be executed like this:
```
php vendor/symfony/phpunit-bridge/bin/simple-phpunit --testsuite unittests
```
Functional tests can be executed like so:
```
php vendor/symfony/phpunit-bridge/bin/simple-phpunit --testsuite functional
```
Browser tests require PhantomJS as well as the application running in the test environment. 
To do that, execute `bash scripts/start-phantomjs.sh` *once* before executing the tests. There is no
need to call the script again until you reboot. Then execute the following to run the browser tests:
```
php vendor/symfony/phpunit-bridge/bin/simple-phpunit --testsuite browser
```

# Running the application in production

## Apache configuration

Install `apache2` and `libapache2-mod-php7.0`. Create a VHost like this:
```
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html/AdventureLookup/web
    <Directory /var/www/html/AdventureLookup/web>
        AllowOverride All
        Order Allow,Deny
        Allow from All
        <IfModule mod_rewrite.c>
            Options -MultiViews
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^(.*)$ app.php [QSA,L]
        </IfModule>
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

For more information about server configuration, checkout the [Symfony guide on server config](https://symfony.com/doc/current/setup/web_server_configuration.html).
Also, adjust adjust `/etc/apache2/conf-enabled/security.conf` to make Apache production-ready.

### Permissions

Make sure to read the [Symfony guide on permissions](https://symfony.com/doc/current/setup/file_permissions.html) if you run into permission problems.

## MySQL

Make sure to run `mysql_secure_installation`. Adjust port, username, host and password in `app/config/parameters.yml`.

## Ports used in development

| Port | Forwarded to host machine | Purpose                                        |
|------|---------------------------|------------------------------------------------|
| 3306 | no                        | MySQL                                          |
| 8000 | yes                       | Application dev server                         |
| 8001 | yes                       | Webpack dev server if run from within Vagrant  |
| 8002 | no                        | Webpack dev server if run from outside Vagrant |
| 8003 | no                        | Application test server                        |
| 8510 | no                        | PhantomJS server                               |
| 9200 | yes                       | ElasticSearch                                  |

# Tools used

- Ubuntu 16.04 as the server
- MySQL 5.7 to store the adventures
- Elasticsearch 5.5 to search the adventures
- PHP7.0 to run the application
- Symfony 3 as the web framework
- Composer as PHP package manager
- NPM 5 as Frontend package manager
- Symfony Encore / Webpack for frontend assets
