FROM php:apache
COPY ./php.ini /usr/local/etc/php/
COPY ./.bashrc /root/.bashrc

# Directorio de trabajo
#
RUN mkdir /src

RUN export TERM=xterm
RUN apt-get update
RUN apt-get install nano -y
RUN apt-get install apt-utils -y
RUN apt-get install wget -y
RUN apt-get install git -y
RUN apt-get install zip unzip -y

COPY ./composer.sh /tmp/composer.sh
RUN sh /tmp/composer.sh

# xdebug
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
COPY ./xdebug.ini /tmp/xdebug.ini
RUN cat /tmp/xdebug.ini >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install opcache
RUN docker-php-ext-install opcache

# Install APCu
RUN pecl install apcu
RUN echo "extension=apcu.so" > /usr/local/etc/php/conf.d/apcu.ini

# Install bcmath
RUN docker-php-ext-install bcmath

# Install bz2
RUN apt-get install -y libbz2-dev
RUN docker-php-ext-install bz2

# Install GD
RUN apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng12-dev
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/
RUN docker-php-ext-install gd

# Install mbstring
RUN docker-php-ext-install mbstring

# Install mcrypt
RUN apt-get install -y libmcrypt-dev
RUN docker-php-ext-install mcrypt

# Install XSL
RUN apt-get install -y libxslt-dev
RUN docker-php-ext-install xsl

# Install xmlrpc
RUN docker-php-ext-install xmlrpc

RUN apt-get update
RUN echo 'deb http://packages.dotdeb.org jessie all' >> /etc/apt/sources.list
RUN wget https://www.dotdeb.org/dotdeb.gpg
RUN apt-key add dotdeb.gpg -y
RUN apt-get update
RUN apt-get install mysql-client -y
RUN apt-get install php7.0-mysql -y
RUN apt-get install php7.0-zip -y
RUN docker-php-ext-install pdo_mysql
RUN a2enmod rewrite headers expires
RUN /etc/init.d/apache2 restart

RUN curl -L -o drush https://github.com/drush-ops/drush/releases/download/8.1.10/drush.phar
RUN chmod +x drush
RUN mv drush /usr/local/bin


