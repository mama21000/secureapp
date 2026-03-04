FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql

RUN a2enmod rewrite headers

RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libwebp-dev \
  && docker-php-ext-configure gd --with-jpeg --with-webp \
  && docker-php-ext-install gd \
  && rm -rf /var/lib/apt/lists/*

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

COPY . /var/www/html

RUN mkdir -p /var/www/html/storage && chown -R www-data:www-data /var/www/html/storage \
&& chmod -R 750 /var/www/html/storage

# Production hardening: don't show errors
RUN echo "display_errors=0" > /usr/local/etc/php/conf.d/99-secure.ini \
 && echo "log_errors=1" >> /usr/local/etc/php/conf.d/99-secure.ini \
 && echo "error_log=/var/www/html/storage/logs/php_errors.log" >> /usr/local/etc/php/conf.d/99-secure.ini
 
# enable ssl module
RUN a2enmod ssl

# copy certs into container
COPY docker/certs/selfsigned.crt /etc/ssl/localcerts/selfsigned.crt
COPY docker/certs/selfsigned.key /etc/ssl/localcerts/selfsigned.key

# ssl vhost config
COPY docker/apache-ssl.conf /etc/apache2/sites-available/default-ssl.conf

# enable ssl site
RUN a2ensite default-ssl

COPY docker/ports.conf /etc/apache2/ports.conf

