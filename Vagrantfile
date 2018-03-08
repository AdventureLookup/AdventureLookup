# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/xenial64"
  config.vm.network "forwarded_port", guest: 8000, host: 8000
  config.vm.network "forwarded_port", guest: 8001, host: 8001
  config.vm.network "forwarded_port", guest: 9200, host: 9200
  config.vm.synced_folder ".", "/vagrant"

  config.vm.network "private_network", type: "dhcp"

  config.vm.provider "virtualbox" do |vb|
     vb.memory = "2048"
     vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
  end
  config.vm.provision "shell", inline: <<-SHELL
     set -ev

     apt-get -y -qq update

     # Create 2GB Swap. Otherwise, some composer operations might run out of memory.
     fallocate -l 2G /swapfile
     chmod 600 /swapfile
     mkswap /swapfile
     swapon /swapfile
     echo "/swapfile   none    swap    sw    0   0" >> /etc/fstab

     # MySQL
     export DEBIAN_FRONTEND=noninteractive
     debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'
     debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'
     apt-get -y -qq install mysql-server libmysqlclient-dev libssl-dev

     # Create Database
     mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS adl"

     # PHP
     apt-get -y -qq install php7.0 php7.0-curl php7.0-fpm php7.0-mysql php7.0-zip php7.0-cli php7.0-xml php7.0-mbstring php7.0-sqlite3 php7.0-intl php-xdebug

     # Increase realpath cache size and ttl for better performance
     sed -i "s/^;realpath_cache_size =$/realpath_cache_size = 4096k/" /etc/php/7.0/cli/php.ini
     sed -i "s/^;realpath_cache_ttl =$/realpath_cache_ttl = 7200/"    /etc/php/7.0/cli/php.ini

     # Utilities
     apt-get -y -qq install htop git nano vim

     # Oracle Java 8
     add-apt-repository -y ppa:webupd8team/java
     apt-get -y -qq update
     echo debconf shared/accepted-oracle-license-v1-1 select true | debconf-set-selections
     echo debconf shared/accepted-oracle-license-v1-1 seen   true | debconf-set-selections
     apt-get -y -qq install oracle-java8-installer > /dev/null

     # Node (JavaScript runtime)
     curl -sL https://deb.nodesource.com/setup_6.x | bash -
     apt-get -y -qq install nodejs

     # Update NPM
     npm install npm@latest -g --loglevel=warn

     # PhantomJS headless browser
     wget -q https://bitbucket.org/ariya/phantomjs/downloads/phantomjs-2.1.1-linux-x86_64.tar.bz2
     tar -xjf phantomjs-2.1.1-linux-x86_64.tar.bz2
     mv phantomjs-2.1.1-linux-x86_64 /opt/phantomjs
     ln -s /opt/phantomjs/bin/phantomjs /usr/bin/phantomjs
     apt-get -y -qq install libfontconfig1

     # Composer (PHP Package Manager)
     bash /vagrant/scripts/install-composer.sh

     # Speed up composer install by parallel downloads
     sudo -u ubuntu -H sh -c "composer global require --no-progress hirak/prestissimo"
     # Display changes for updated packages
     sudo -u ubuntu -H sh -c "composer global require --no-progress pyrech/composer-changelogs"

     # Elasticsearch
     wget -q https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-5.5.0.deb
     dpkg -i elasticsearch-5.5.0.deb
     systemctl enable elasticsearch.service
     rm elasticsearch-5.5.0.deb
     service elasticsearch start

     ### Development Elasticsearch settings:
     # Decrease memory to 256MB
     sed -i -e 's/2g/256m/g' /etc/elasticsearch/jvm.options
     # Listen on 0.0.0.0
     echo "network.host: 0.0.0.0" >> /etc/elasticsearch/elasticsearch.yml

     echo "cd /vagrant" >> /home/ubuntu/.bashrc
  SHELL
end
