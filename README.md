# Adventure Lookup

Repository of Adventure Lookup, proposed by [/u/mattcolville](https://www.reddit.com/user/mattcolville).

# Setting up a development environment

To get you up and running quickly, you should use [Vagrant](https://vagrantup.com) and [VirtualBox](https://virtualbox.org) to start up a VM with all the dependencies preinstalled.

After downloading and installing Vagrant and VirtualBox, clone this repository and navigate into the folder. Then execute `vagrant up` to create and boot the VM. This will take a bit longer on the initial boot. When the VM has booted, execute `vagrant ssh` to SSH into the VM. You should be right inside the `/vagrant` folder which contains all the application's files.

Execute the following commands to finish the installation:
```
# Install PHP dependencies
composer install -n

# Install Frontend dependencies
yarn install --no-bin-links
nodejs node_modules/node-sass/scripts/install.js
npm rebuild node-sass --no-bin-links

# Setup database
php bin/console doctrine:migrations:migrate

# Create Elasticsearch index
php bin/console app:elasticsearch:reindex

# Import dummy adventures
php bin/console doctrine:fixtures:load --fixtures src/AppBundle/DataFixtures/ORM/TagData.php --fixtures src/AppBundle/DataFixtures/ORM/RandomAdventuresData.php -n
php bin/console app:elasticsearch:reindex
```

If you didn't use Vagrant and use an existing MySQL database, adjust the `app/config/parameters.yml` file to match your database credentials.

# Prepare web assets for EasyAdmin
php bin/console assets:install --symlink

# Running the application

```
# Start Symfony development server on port 8000 to run the application
php bin/console server:start 0.0.0.0

# Start webpack to watch changes to assets and recompile them
nodejs node_modules/webpack/bin/webpack --watch --progress
```

The application is now running at http://localhost:8000/app_dev.php.
Elasticsearch can be accessed at http://localhost:9200.

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

# Tools used

- Ubuntu 16.04 as the server
- MySQL 5.7 to store the adventures
- Elasticsearch 5.5 to search the adventures
- PHP7.0 to run the application
- Symfony 3 as the web framework
- Composer as PHP package manager
- Yarn as Frontend package manager
- Webpack for frontend assets
