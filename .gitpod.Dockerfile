FROM gitpod/workspace-mysql

USER gitpod

RUN wget -q https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-7.16.0-linux-x86_64.tar.gz \
  && tar -xzf elasticsearch-7.16.0-linux-x86_64.tar.gz

# Save ElasticSearch data in a subfolder of the /workspace directory. Otherwise it is lost when
# restarting the workspace.
RUN echo "path.data: /workspace/elasticsearch" >> elasticsearch-7.16.0/config/elasticsearch.yml
# Do not set indices to readonly when disk space is low
RUN echo "cluster.routing.allocation.disk.threshold_enabled: false" >> elasticsearch-7.16.0/config/elasticsearch.yml
# Disable security features
RUN echo "xpack.security.enabled: false" >> elasticsearch-7.16.0/config/elasticsearch.yml
# Disable discovery of other nodes
RUN echo "discovery.type: single-node" >> elasticsearch-7.16.0/config/elasticsearch.yml
# Decrease ElasticSearch memory usage
RUN printf '%s\n%s\n' "-Xms256m" "-Xmx256m" > elasticsearch-7.16.0/config/jvm.options.d/adventurelookup.options

# Install and use PHP 7.4
RUN sudo apt-get update \
  && sudo add-apt-repository -y ppa:ondrej/php \
  && sudo apt-get install -y php7.4 php7.4-curl php7.4-fpm php7.4-mysql php7.4-zip php7.4-cli php7.4-xml php7.4-mbstring php7.4-sqlite3 php7.4-intl php-xdebug \
  && sudo rm -rf /var/lib/apt/lists/* \
  && sudo update-alternatives --set php /usr/bin/php7.4

# Install Google Chrome
RUN wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | sudo apt-key add - \
  && echo 'deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main' | sudo tee /etc/apt/sources.list.d/google-chrome.list \
  && sudo apt-get update \
  && sudo apt-get -y install google-chrome-stable \
  && sudo rm -rf /var/lib/apt/lists/*

# Re-install composer, because the version shipped with GitPod depends on the pre-installed PHP version
# that might change if Gitpod updates their containers.
RUN curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/bin --filename=composer --1

COPY scripts/xdebug-gitpod.ini /etc/php/7.4/cli/conf.d/30-adl-xdebug.ini