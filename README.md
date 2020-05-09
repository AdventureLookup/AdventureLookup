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



To get you up and running quickly, you have three options to set up your development environment:

1. Gitpod online IDE
2. Vagrant with VirtualBox
3. Vagrant with Docker

Using Gitpod is by far the simplest approach: All you have to do is click the following button and sign in with GitHub. A VSCode-based online IDE will spin up with all dependencies preinstalled. The rest of this section is only relevant when using Vagrant.

[![Gitpod Open-Online-IDE](https://img.shields.io/badge/Gitpod-Open--Online--IDE-blue?logo=gitpod)](https://gitpod.io/#https://github.com/AdventureLookup/AdventureLookup)

If you don't want to use an online IDE, but instead use your own editor, you should use [Vagrant](https://vagrantup.com) and either [VirtualBox](https://virtualbox.org) or [Docker](https://www.docker.com/) to start up a VM/container with all the dependencies preinstalled.

Using VirtualBox is recommended if you have are using Windows without Hyper-V available/enabled. In all other cases, Docker is recommended and faster.

After downloading and installing Vagrant and either VirtualBox or Docker:

```bash
# Clone the repo to your local machine (this is readonly; you need to fork if you want write)
git clone git@github.com:AdventureLookup/AdventureLookup.git

cd AdventureLookup

# Create and provision the VM
# This takes quite some time on the very first start

# If you installed Docker:
vagrant up --provider=docker

# If you installed VirtualBox
vagrant up --provider=virtualbox
```

Execute the following commands to finish the installation:
```bash
# Log into the VM
vagrant ssh
# You should be inside the /vagrant folder.

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
```bash
# Creates 'user', 'curator' and 'admin' users, all with password 'asdf'
php bin/console doctrine:fixtures:load --append --fixtures src/AppBundle/DataFixtures/ORM/TestUserData.php
```

If you didn't use Vagrant but an existing MySQL database, adjust the `app/config/parameters.yml` file to match your database credentials.

### Running the application

```bash
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
```bash
php vendor/symfony/phpunit-bridge/bin/simple-phpunit --testsuite unittests
```
Functional tests can be executed like so:
```bash
php vendor/symfony/phpunit-bridge/bin/simple-phpunit --testsuite functional
```
Browser tests require Google Chrome with remote debugging enabled as well as the application running in the test environment.
To do that, execute `bash scripts/prepare-browser-tests.sh` *once* before executing the tests. There is no
need to call the script again until you reboot. Then execute the following to run the browser tests:
```bash
npm run prod
php bin/console cache:clear --env test
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
- Elasticsearch 7.6.2 to search the adventures
- PHP7.4 to run the application
- Symfony 3 as the web framework
- Composer as PHP package manager
- Node.js 12 and npm 6 for frontend package management
- Symfony Encore / Webpack for bundling frontend assets

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