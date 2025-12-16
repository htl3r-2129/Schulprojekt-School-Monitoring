FROM php:8.2-apache

# Enable mysqli extension
RUN apt-get update && \
    apt-get install -y libmariadb-dev && \
    docker-php-ext-install mysqli

RUN rm -rf /usr/local/apache2/htdocs/*
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer
RUN apt-get update
RUN apt-get install -y git

COPY . /var/www/html

COPY apache/vhost.conf /usr/local/apache2/conf/conf.d/vhost.conf
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer
COPY ./composer.json /var/www/html/composer.json

RUN composer install

RUN echo "IncludeOptional conf/conf.d/vhost.conf" >> /usr/local/apache2/conf/httpd.conf

EXPOSE 80
