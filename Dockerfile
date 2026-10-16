# pdo_sqlite and mbstring ship compiled into the official php image.
FROM php:8.2-apache

# Debian's apache2.conf sets AllowOverride None for /var/www; the app relies on
# .htaccess to deny HTTP access to app/ and storage/ (SQLite database).
RUN a2enmod rewrite headers \
    && sed -ri '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
    && echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && sed -ri 's/^ServerTokens .*/ServerTokens Prod/; s/^ServerSignature .*/ServerSignature Off/' /etc/apache2/conf-available/security.conf \
    && printf 'expose_php = Off\ndate.timezone = Asia/Jakarta\n' > /usr/local/etc/php/conf.d/zz-app.ini \
    && php -m | grep -qi '^pdo_sqlite$' \
    && php -m | grep -qi '^mbstring$'

WORKDIR /var/www/html

COPY --chown=root:root . /var/www/html/

RUN find /var/www/html -type d -exec chmod 755 {} + \
    && find /var/www/html -type f -exec chmod 644 {} + \
    && mkdir -p storage \
    && chown -R www-data:www-data storage \
    && chmod 750 storage

EXPOSE 80

CMD ["apache2-foreground"]
