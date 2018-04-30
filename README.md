# Adventure Lookup

Repository of Adventure Lookup, proposed by [/u/mattcolville](https://www.reddit.com/user/mattcolville).

| Branch | Travis CI                                        | Code Analysis                          | Link                            | 
| ------ | ------------------------------------------------ | -------------------------------------- | ------------------------------- | 
| master | [![Build Status][travis-svg-master]][travis-url] | -                                      | https://adventurelookup.com     |
| dev    | [![Build Status][travis-svg-dev]][travis-url]    | [![codecov][codecov-svg]][codecov-url] | https://dev.adventurelookup.com |

[travis-url]:        https://travis-ci.org/AdventureLookup/AdventureLookup
[travis-svg-master]: https://travis-ci.org/AdventureLookup/AdventureLookup.svg?branch=master
[travis-svg-dev]:    https://travis-ci.org/AdventureLookup/AdventureLookup.svg?branch=dev

[codecov-url]: https://codecov.io/gh/AdventureLookup/AdventureLookup
[codecov-svg]: https://codecov.io/gh/AdventureLookup/AdventureLookup/branch/dev/graph/badge.svg

## Setting up a development environment

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

# You should be inside the /vagrant folder.
```

Execute the following commands to finish the installation:
```
# Install PHP dependencies
composer install -n --no-suggest
 
# Install Frontend dependencies, can be run outside the virtual machine
npm install

# Setup database (confirm with 'y')
php bin/console doctrine:migrations:migrate
 
# Create Elasticsearch index
php bin/console app:elasticsearch:reindex
 
# Import dummy adventures (confirm with 'y')
php bin/console doctrine:fixtures:load --fixtures src/AppBundle/DataFixtures/ORM/RandomAdventureData.php
php bin/console app:elasticsearch:reindex
```

You can execute the following command to create dummy users:
```
# Creates 'user', 'curator' and 'admin' users, all with password 'asdf'
php bin/console doctrine:fixtures:load --append --fixtures src/AppBundle/DataFixtures/ORM/TestUserData.php
```

If you didn't use Vagrant but an existing MySQL database, adjust the `app/config/parameters.yml` file to match your database credentials.

### Running the application

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

# Wait until you see 'DONE Compiled successfully in XXXXms' (may take a few seconds)
```

The application is now running at http://localhost:8000.
ElasticSearch can be accessed at http://localhost:9200.

### Running tests

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
Browser tests require Google Chrome with remote debugging enabled as well as the application running in the test environment. 
To do that, execute `bash scripts/prepare-browser-tests.sh` *once* before executing the tests. There is no
need to call the script again until you reboot. Then execute the following to run the browser tests:
```
php vendor/symfony/phpunit-bridge/bin/simple-phpunit --testsuite browser
```

### Ports used in development

| Port | Forwarded to host machine | Purpose                                           |
|------|---------------------------|---------------------------------------------------|
| 3306 | no                        | MySQL                                             |
| 5900 | yes                       | VNC server (see scripts/prepare-browser-tests.sh) |
| 8000 | yes                       | Application dev server                            |
| 8001 | yes                       | Webpack dev server if run from within Vagrant     |
| 8002 | no                        | Webpack dev server if run from outside Vagrant    |
| 8003 | no                        | Application test server                           |
| 9200 | yes                       | ElasticSearch                                     |
| 9222 | no                        | Chrome Remote Debugging                           |

## Contributing

AdventureLookup is an open-source project. We are trying to make contributing as easy
as possible by providing the Vagrant image described above. If you run into any issues
while setting up your development environment, please let us know by opening an issue.
If you're ready to work on your first issue or create your first pull request, please
checkout the [CONTRIBUTING.md](CONTRIBUTING.md) file.

## Tools used

- Ubuntu 16.04 as the server
- MySQL 5.7 to store the adventures
- Elasticsearch 5.5 to search the adventures
- PHP7.0 to run the application
- Symfony 3 as the web framework
- Composer as PHP package manager
- NPM 5 as Frontend package manager
- Symfony Encore / Webpack for frontend assets

## Running the application in production

For information about server configuration, checkout the [Symfony guide on server config](https://symfony.com/doc/current/setup/web_server_configuration.html).
Make sure to read the [Symfony guide on permissions](https://symfony.com/doc/current/setup/file_permissions.html) if you run into permission problems.

You should configure your websever to set never-expiring cache headers for
the /assets path (located in web/assets). Example Nginx configuration:

```nginx
location /assets {
    expires max;
    add_header Pragma public;
    add_header Cache-Control "public";
}
```