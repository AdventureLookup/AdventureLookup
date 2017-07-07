# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/xenial64"
  config.vm.network "forwarded_port", guest: 8000, host: 8000
  config.vm.network "forwarded_port", guest: 9200, host: 9200

  if Vagrant::Util::Platform.windows? then
    puts "Windows OS detected - using slow shared folders."
    config.vm.synced_folder ".", "/vagrant"
  else
    puts "Non-Windows OS detected - using nfs to share folder."
    config.vm.synced_folder ".", "/vagrant", :nfs => true
  end

  config.vm.network "private_network", type: "dhcp"

  config.vm.provider "virtualbox" do |vb|
     vb.memory = "2048"
     vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
  end
  config.vm.provision "shell", inline: <<-SHELL
     apt -y update

     # MySQL
     export DEBIAN_FRONTEND=noninteractive
     debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'
     debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'
     apt -y install mysql-server libmysqlclient-dev libssl-dev

     # Create Database
     mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS adl"

     # PHP
     apt -y install php7.0 php7.0-curl php7.0-fpm php7.0-mysql php7.0-zip php7.0-cli php7.0-xml

     # Utilities
     apt -y install htop git nano vim

     # Apache (not needed for development)
     # apache2 libapache2-mod-php7.0

     # Oracle Java 8
     add-apt-repository -y ppa:webupd8team/java
     apt -y update
     echo debconf shared/accepted-oracle-license-v1-1 select true | sudo debconf-set-selections
     echo debconf shared/accepted-oracle-license-v1-1 seen true | sudo debconf-set-selections
     apt -y install oracle-java8-installer

     # Node (JavaScript runtime)
     curl -sL https://deb.nodesource.com/setup_6.x | bash -
     apt install -y nodejs

     # Yarn (Frontend Package Manager)
     curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
     echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
     apt -y update
     apt -y install yarn

     # Composer (PHP Package Manager)
     php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
     php -r "if (hash_file('SHA384', 'composer-setup.php') === '669656bab3166a7aff8a7506b8cb2d1c292f042046c5a994c43155c0be6190fa0355160742ab2e1c88d40d5be660b410') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
     php composer-setup.php --install-dir=/usr/bin --filename=composer
     php -r "unlink('composer-setup.php');"

     # Elasticsearch
     wget https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-5.5.0.deb
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
