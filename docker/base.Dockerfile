FROM ubuntu:22.04

ARG WWWUSER=501
ARG WWWGROUP=20
ARG NODE_VERSION=20
ARG POSTGRES_VERSION=15
ARG PHP_VERSION=8.3

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Create necessary directories for APT keys
RUN mkdir -p /etc/apt/keyrings

# Update and install initial dependencies
RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    apt-get update && apt-get install -y \
    gnupg \
    curl \
    ca-certificates \
    wget \
    software-properties-common \
    sudo \
    inotify-tools \
    gosu

# Install yq
RUN wget -qO /usr/local/bin/yq https://github.com/mikefarah/yq/releases/latest/download/yq_linux_amd64 && \
    chmod +x /usr/local/bin/yq

# Add PHP repository and key correctly
RUN curl -fsSL "https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c" | gpg --dearmor | tee /etc/apt/keyrings/ppa_ondrej_php.gpg > /dev/null && \
    echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu jammy main" > /etc/apt/sources.list.d/ppa_ondrej_php.list

# Install base packages
RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    apt-get update && apt-get install -y --fix-missing \
    zip \
    unzip \
    git \
    supervisor \
    sqlite3 \
    libcap2-bin \
    libpng-dev \
    python2 \
    dnsutils \
    librsvg2-bin \
    fswatch \
    ffmpeg \
    nano \
    chromium-browser \
    libbrotli-dev \
    tesseract-ocr \
    libtesseract-dev \
    libleptonica-dev \
    ghostscript \
    libmagickwand-dev \
    poppler-utils \
    jq

# Install PHP and extensions
RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    apt-get update && apt-get install -y --fix-missing \
    php${PHP_VERSION}-cli \
    php${PHP_VERSION}-dev \
    php${PHP_VERSION}-pgsql \
    php${PHP_VERSION}-sqlite3 \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-imap \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-zip \
    php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-soap \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-readline \
    php${PHP_VERSION}-ldap \
    php${PHP_VERSION}-msgpack \
    php${PHP_VERSION}-igbinary \
    php${PHP_VERSION}-redis \
    php${PHP_VERSION}-memcached \
    php${PHP_VERSION}-pcov \
    php${PHP_VERSION}-xdebug \
    php${PHP_VERSION}-imagick

# Install Composer
RUN curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

# Add NodeJS repository and install Node
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg && \
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_${NODE_VERSION}.x nodistro main" > /etc/apt/sources.list.d/nodesource.list && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    apt-get update && apt-get install -y --fix-missing nodejs && \
    npm install -g npm

# Add PostgreSQL repository and install client
RUN curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor -o /etc/apt/keyrings/pgdg.gpg && \
    echo "deb [signed-by=/etc/apt/keyrings/pgdg.gpg] http://apt.postgresql.org/pub/repos/apt jammy-pgdg main" > /etc/apt/sources.list.d/pgdg.list && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    apt-get update && apt-get install -y --fix-missing postgresql-client-${POSTGRES_VERSION}

# Install PHP PECL extensions
RUN pecl install excimer && \
    echo "extension=excimer.so" > /etc/php/${PHP_VERSION}/cli/conf.d/20-excimer.ini

# Copy Xdebug configuration
COPY docker/common/xdebug.ini /etc/php/${PHP_VERSION}/cli/conf.d/99-xdebug.ini
COPY docker/common/xdebug.ini /etc/php/${PHP_VERSION}/fpm/conf.d/99-xdebug.ini

# Create directories for Xdebug
RUN mkdir -p /var/www/html/coverage && \
    chown -R www-data:dialout /var/www/html/coverage && \
    chmod -R 775 /var/www/html/coverage

# Configure ImageMagick policy
RUN sed -i 's/<policy domain="coder" rights="none" pattern="PDF" \/>/<policy domain="coder" rights="read|write" pattern="PDF" \/>/g' /etc/ImageMagick-6/policy.xml

# Set PHP capabilities
RUN setcap "cap_net_bind_service=+ep" /usr/bin/php${PHP_VERSION}

# Create directory structure
RUN mkdir -p /var/www/html \
    /var/www/.config/composer \
    /var/www/.composer \
    /var/www/.cache \
    /var/www/.npm

# Git configurations
RUN git config --system core.fileMode false && \
    git config --system --add safe.directory '*' && \
    git config --system core.autocrlf false

# Setup user and permissions - using dialout group directly
RUN deluser www-data || true && \
    useradd -u ${WWWUSER} -m -s /bin/bash -d /var/www www-data && \
    usermod -aG dialout www-data && \
    usermod -aG sudo www-data && \
    echo "www-data ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers && \
    chown -R www-data:dialout /var/www && \
    chmod -R 775 /var/www

# Cleanup
RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/*

USER www-data
ENV HOME=/var/www

EXPOSE 80