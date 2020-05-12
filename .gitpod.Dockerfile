FROM gitpod/workspace-mysql
                    
USER gitpod

RUN wget -q https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-5.5.3.tar.gz \
  && tar -xzf elasticsearch-5.5.3.tar.gz

# Save ElasticSearch data in a subfolder of the /workspace directory. Otherwise it is lost when
# restarting the workspace.
RUN echo "path.data: /workspace/elasticsearch" >> elasticsearch-5.5.3/config/elasticsearch.yml
# Decrease ElasticSearch memory usage
RUN sed -i -e 's/2g/256m/g' elasticsearch-5.5.3/config/jvm.options

# Install and use PHP 7.0
RUN sudo apt-get update \
  && sudo add-apt-repository -y ppa:ondrej/php \
  && sudo apt-get install -y php7.0 php7.0-curl php7.0-fpm php7.0-mysql php7.0-zip php7.0-cli php7.0-xml php7.0-mbstring php7.0-sqlite3 php7.0-intl php-xdebug \
  && sudo rm -rf /var/lib/apt/lists/* \
  && sudo update-alternatives --set php /usr/bin/php7.0

# Re-install composer, because the version shipped with GitPod requires PHP7.4
RUN curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/bin --filename=composer

# Set authentication plugin to mysql_native_password to fix compatibility with PHP7.0
# https://www.php.net/manual/de/mysqli.requirements.php
RUN echo '[mysqld]\ndefault-authentication-plugin=mysql_native_password' > /etc/mysql/mysql.conf.d/php7.0-fix.cnf