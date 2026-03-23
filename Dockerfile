# ─── SCC School Management System — Railway Dockerfile ───────────────────────
# PHP 8.2 with Apache

FROM php:8.2-apache

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        mysqli \
        pdo \
        pdo_mysql \
        gd \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Fix MPM conflict - disable event, enable prefork
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork

# Enable Apache modules
RUN a2enmod rewrite headers expires deflate

# Configure Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy Apache virtual host config
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Create uploads directory and set permissions
RUN mkdir -p uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/uploads

# Copy PHP ini settings
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Startup script that sets Apache port from Railway's $PORT env var
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]
