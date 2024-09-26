#
# October CMS installer
#
# Create image with:
#   docker build -t myoctober:latest .
#
# Open installer with:
#   http://localhost:port/install.php
#
FROM php:8.2-apache
LABEL maintainer="October CMS <hello@octobercms.com> (@octobercms)"

# Installs dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    wget \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libyaml-dev \
    libwebp-dev \
    libzip4 \
    libzip-dev \
    zlib1g-dev \
    libicu-dev \
    libpq-dev \
    libsqlite3-dev \
    g++ \
    git \
    cron \
    vim \
    nano \
    ssh-client \
    && docker-php-ext-install opcache \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install zip \
    && docker-php-ext-install exif \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install pdo_pgsql \
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Sets recommended PHP.ini settings (https://secure.php.net/manual/en/opcache.installation.php)
RUN { \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=4000'; \
    echo 'opcache.revalidate_freq=2'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.enable_cli=1'; \
    echo 'upload_max_filesize=128M'; \
    echo 'post_max_size=128M'; \
    echo 'expose_php=off'; \
    } > /usr/local/etc/php/conf.d/php-recommended.ini

RUN pecl install apcu \
    && pecl install yaml-2.2.2 \
    && docker-php-ext-enable apcu yaml

# Enables apache rewrite w/ security
RUN a2enmod rewrite expires && \
    sed -i 's/ServerTokens OS/ServerTokens ProductOnly/g' \
    /etc/apache2/conf-available/security.conf

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Create html directory
RUN mkdir -p /var/www/html && chown -R www-data:www-data /var/www/html

# Sets user to www-data
USER www-data

# Adds installer
WORKDIR /var/www/html

# Download the zip file
RUN wget -O installer.zip https://github.com/octobercms/install/archive/refs/heads/master.zip

# Unzip the downloaded file
RUN unzip installer.zip -d /var/www/html && \
    mv /var/www/html/install-master/* /var/www/html/ && \
    rm -r /var/www/html/install-master && \
    rm installer.zip

# Returns to root user
USER root

# Expose the default port
EXPOSE 80/tcp

# Provides container inside image for data persistence
VOLUME ["/var/www/html"]

CMD ["apache2-foreground"]
