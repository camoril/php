# Dockerfile para Sistema de Hojas de Servicio
FROM php:8.4.11-apache

# Metadata
LABEL maintainer="Ernesto Pineda <camoril@gmail.com>"
LABEL description="Sistema de Hojas de Servicio - Gestión de intervenciones con firma digital"
LABEL version="0.0.1-beta2"

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Configurar Apache para permitir .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Crear directorio de PDFs con permisos
RUN mkdir -p /var/www/html/pdfs && \
    chown -R www-data:www-data /var/www/html/pdfs && \
    chmod -R 755 /var/www/html/pdfs

# Configurar directorio de sesiones de PHP
RUN mkdir -p /var/lib/php/sessions && \
    chown -R www-data:www-data /var/lib/php/sessions && \
    chmod -R 755 /var/lib/php/sessions && \
    echo "session.save_path = \"/var/lib/php/sessions\"" > /usr/local/etc/php/conf.d/sessions.ini

# Copiar archivos de la aplicación
COPY --chown=www-data:www-data . /var/www/html/

# Copiar script de entrada
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Exponer puerto 80
EXPOSE 80

# Usar script de entrada personalizado
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
