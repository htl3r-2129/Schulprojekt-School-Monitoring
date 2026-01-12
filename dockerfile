FROM php:8.2-apache

# Enable mysqli extension
RUN apt-get update
RUN apt-get install -y libmariadb-dev curl git ssl-cert
RUN docker-php-ext-install mysqli

RUN rm -rf /usr/local/apache2/htdocs/*
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer

COPY . /var/www/html

# Kopiere alle Anwendungsdateien
COPY ./public/ /var/www/html/public/
COPY ./src /var/www/html/src
COPY apache/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY ./composer.json /var/www/html/composer.json

# Enable SSL module
RUN a2enmod ssl

# Enable the SSL virtual host
RUN a2ensite default-ssl

# Führe Composer Install aus (entfernt PHPMailer)
RUN composer update
RUN composer install

# Setze Schreibrechte für den public-Ordner (falls nötig)
RUN chown -R www-data:www-data /var/www/html/public

# Konfiguriere Apache (aktiviert mod_rewrite und vhost.conf)
RUN a2enmod rewrite
RUN echo "IncludeOptional /etc/apache2/sites-available/*.conf" >> /etc/apache2/apache2.conf

EXPOSE 80
