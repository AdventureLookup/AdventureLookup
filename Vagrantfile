# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.network "forwarded_port", guest: 5900, host: 5900
  config.vm.network "forwarded_port", guest: 8000, host: 8000
  config.vm.network "forwarded_port", guest: 8001, host: 8001
  config.vm.network "forwarded_port", guest: 9200, host: 9200
  config.vm.synced_folder ".", "/vagrant"

  config.vm.network "private_network", type: "dhcp"

  config.vm.provider "virtualbox" do |vb, override|
    override.vm.box = "ubuntu/bionic64"
    vb.memory = "2048"
    vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
  end

  config.vm.provider "docker" do |d, override|
    d.build_dir = "./scripts/vagrant-docker"
    d.remains_running = true
    d.has_ssh = true
    override.ssh.username = "vagrant"
    override.ssh.password = "vagrant"
  end

  config.vm.provision "initial", type: "shell", inline: <<-SHELL
     # Make apt-get commands as quiet as possible by using ""-qq -o=Dpkg::Use-Pty=0"
     # https://askubuntu.com/a/668859
     function apt_quiet {
       apt-get -y -qq -o=Dpkg::Use-Pty=0 ${@}
     }

     function is_docker {
      if [ -f /.dockerenv ]; then
          return 0
      else
          return 1
      fi
     }

     set -ev

     apt_quiet update
     # Install software-properties-common for add-apt-repository
     apt_quiet install software-properties-common

     if is_docker; then
        echo Skipping swap creation for docker container
     else
        # Create 2GB Swap. Otherwise, some composer operations might run out of memory.
        fallocate -l 2G /swapfile
        chmod 600 /swapfile
        mkswap /swapfile
        swapon /swapfile
        echo "/swapfile   none    swap    sw    0   0" >> /etc/fstab
     fi

     # MySQL
     export DEBIAN_FRONTEND=noninteractive
     debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'
     debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'
     apt_quiet install mysql-server libmysqlclient-dev libssl-dev

     # Create Database
     mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS adl"

     # PHP
     add-apt-repository -y ppa:ondrej/php
     apt_quiet install php7.4 php7.4-curl php7.4-fpm php7.4-mysql php7.4-zip php7.4-cli php7.4-xml php7.4-mbstring php7.4-sqlite3 php7.4-intl php7.4-xdebug

     # Increase realpath cache size and ttl for better performance
     sed -i "s/^;realpath_cache_size =$/realpath_cache_size = 4096k/" /etc/php/7.4/cli/php.ini
     sed -i "s/^;realpath_cache_ttl =$/realpath_cache_ttl = 7200/"    /etc/php/7.4/cli/php.ini

     # Utilities
     apt_quiet install htop nano vim unzip curl wget software-properties-common

     # Install a more recent git version. This is needed for husky and lint-staged to work.
     add-apt-repository -y ppa:git-core/ppa
     apt_quiet update
     apt_quiet install git

     # OpenJDK 8
     apt_quiet install openjdk-8-jre > /dev/null

     # Node (JavaScript runtime)
     curl -sL https://deb.nodesource.com/setup_12.x | bash -
     apt_quiet install nodejs

     # Install latest NPM 6.x
     npm install npm@^6 -g --loglevel=warn

     # Headless testing utilities
     apt_quiet install xvfb x11vnc fluxbox
     # Chrome
     wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add -
     echo 'deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main' | tee /etc/apt/sources.list.d/google-chrome.list
     apt_quiet update
     apt_quiet install google-chrome-stable

     # Composer (PHP Package Manager)
     bash /vagrant/scripts/install-composer.sh

     if [ -d "/home/vagrant" ]; then
       # Speed up composer install by parallel downloads
       sudo -u vagrant -H sh -c "composer global require --quiet -n hirak/prestissimo"
       # Display changes for updated packages
       sudo -u vagrant -H sh -c "composer global require --quiet -n pyrech/composer-changelogs"
     fi
     if [ -d "/home/ubuntu" ]; then
       # Speed up composer install by parallel downloads
       sudo -u ubuntu -H sh -c "composer global require --quiet -n hirak/prestissimo"
       # Display changes for updated packages
       sudo -u ubuntu -H sh -c "composer global require --quiet -n pyrech/composer-changelogs"
     fi

     # Elasticsearch
     wget -q https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-7.6.2-amd64.deb
     dpkg -i elasticsearch-7.6.2-amd64.deb
     systemctl enable elasticsearch.service
     rm elasticsearch-7.6.2-amd64.deb
     service elasticsearch start

     ### Development Elasticsearch settings:
     # Decrease memory to 256MB
     sed -i -e 's/1g/256m/g' /etc/elasticsearch/jvm.options
     # Listen on 0.0.0.0
     echo "http.host: 0.0.0.0" >> /etc/elasticsearch/elasticsearch.yml
  SHELL

  # This provisioner must be below the other provisioner. Otherwise it would
  # try to start services before they are installed.
  config.vm.provision "always", type: "shell", run: "always", inline: <<-SHELL
    # Previous versions of the Ubuntu Vagrant box only had the "ubuntu" user.
    # As of January 2018, newer boxes also have a "vagrant" user. People who
    # update their Vagrant box will automatically switch to the new user
    # the next time they "vagrant ssh". This provisioner makes sure the
    # .bashrc files always contains the 'cd /vagrant' line.
    if [ -d "/home/vagrant" ]; then
      if ! grep -q "cd /vagrant" /home/vagrant/.bashrc ; then
        echo "cd /vagrant" >> /home/vagrant/.bashrc
      fi
    fi
    if [ -d "/home/ubuntu" ]; then
      if ! grep -q "cd /vagrant" /home/ubuntu/.bashrc ; then
        echo "cd /vagrant" >> /home/ubuntu/.bashrc
      fi
    fi

    # These do not start automatically in the Docker provisioner.
    service mysql start
    service elasticsearch start
  SHELL
end
