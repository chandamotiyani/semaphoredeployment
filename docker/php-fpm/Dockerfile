
FROM php:7.3-fpm
WORKDIR /app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

# Install the soap extension
RUN apt-get update -yqq
RUN rm /etc/apt/preferences.d/no-debian-php
RUN apt-get -y install libxml2-dev php-soap
RUN docker-php-ext-install soap

# mysql
#RUN docker-php-ext-install mysql mysqli

RUN docker-php-ext-install mbstring

#ZIP
RUN apt-get -y install zip libzip-dev
RUN pecl install zip
RUN docker-php-ext-enable zip

#PDO
RUN docker-php-ext-install pdo pdo_mysql

# GD Image
RUN apt-get -y install libpng-dev
RUN docker-php-ext-install gd

#IMAGICK
#RUN pecl install imagick
#RUN docker-php-ext-enable imagick

#install noejs & npm
RUN apt-get update -yqq
RUN apt-get install -y npm
#bugfix because debians package is incompatable
RUN npm install npm@latest -g

#install gulp
RUN npm install -g gulp


