FROM php:8.2-apache
LABEL maintainer="October CMS <hello@octobercms.com> (@octobercms)"

RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    wget \
    curl \
    make \
    autoconf \
    pkg-config \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libyaml-dev \
    libwebp-dev \
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
    && docker-php-ext-install -j"$(nproc)" gd zip exif mysqli pdo_pgsql pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# PHP recommended settings
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

# PECL extensions (needs make/autoconf)
RUN pecl install apcu \
    && pecl install yaml-2.2.2 \
    && docker-php-ext-enable apcu yaml

RUN a2enmod rewrite expires \
    && sed -i 's/ServerTokens OS/ServerTokens ProductOnly/' /etc/apache2/conf-available/security.conf

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Web root
RUN mkdir -p /var/www/html && chown -R www-data:www-data /var/www/html
USER www-data
WORKDIR /var/www/html

# October installer
RUN wget -O installer.zip https://github.com/octobercms/install/archive/refs/heads/master.zip \
    && unzip installer.zip -d /var/www/html \
    && mv /var/www/html/install-master/* /var/www/html/ \
    && rm -r /var/www/html/install-master installer.zip

USER root
EXPOSE 80
VOLUME ["/var/www/html"]
CMD ["apache2-foreground"]
