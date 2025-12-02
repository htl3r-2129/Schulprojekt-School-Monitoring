FROM php:8.2-apache

# Setze das Arbeitsverzeichnis
WORKDIR /var/www/html

# Installiere notwendige Pakete und PHP-Erweiterungen
# NEU: sendmail wird hinzugefügt, um die PHP mail() Funktion zu unterstützen.
# Der Rest wird für DB, Composer etc. beibehalten.
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    sendmail \
    && rm -rf /var/lib/apt/lists/*

# Installiere PHP-Erweiterungen
RUN docker-php-ext-install pdo pdo_mysql \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install curl

# Composer Installation
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer

# Entferne Standard-Apache-Dateien
RUN rm -rf /var/www/html/*

# Kopiere alle Anwendungsdateien
COPY ./public/ /var/www/html/public/
COPY ./src /var/www/html/src
COPY apache/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY ./composer.json /var/www/html/composer.json

# Führe Composer Install aus (entfernt PHPMailer)
RUN composer install --no-dev

# Setze Schreibrechte für den public-Ordner (falls nötig)
RUN chown -R www-data:www-data /var/www/html/public

# Konfiguriere Apache (aktiviert mod_rewrite und vhost.conf)
RUN a2enmod rewrite
RUN echo "IncludeOptional /etc/apache2/sites-available/*.conf" >> /etc/apache2/apache2.conf

EXPOSE 80