FROM wordpress:5.4.1

RUN apt-get update && apt-get install -y vim

# COPY ./.docker/production/apache/000-default.conf /etc/apache2/sites-available
# COPY ./.docker/production/apache/000-default.conf /etc/apache2/sites-enabled
# COPY ./.docker/production/apache/ports.conf /etc/apache2
COPY ./.docker/production/wordpress/php.ini /usr/local/etc/php/php.ini

COPY --chown=www-data:www-data ./wordpress /var/www/html
COPY --chown=www-data:www-data ./wordpress/wp-config.prod.php /var/www/html/wp-config.php