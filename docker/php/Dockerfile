# =============================================================================
# docker/php/Dockerfile
# PHP 8.3-FPM with all extensions needed for khunnatt.online
# =============================================================================

FROM php:8.3-fpm-alpine

LABEL maintainer="KHUN NATT <khunnatt.online>"

# ── System dependencies ────────────────────────────────────────────────────────
RUN apk add --no-cache \
    libpng-dev        \
    libjpeg-turbo-dev \
    freetype-dev      \
    libzip-dev        \
    icu-dev           \
    oniguruma-dev     \
    curl

# ── PHP extensions ─────────────────────────────────────────────────────────────
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo          \
        pdo_mysql    \
        gd           \
        zip          \
        intl         \
        mbstring     \
        exif         \
        opcache

# ── PHP production-like configuration ──────────────────────────────────────────
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.revalidate_freq=0'; \
    echo 'opcache.validate_timestamps=1'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.memory_consumption=192'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.fast_shutdown=1'; \
} > /usr/local/etc/php/conf.d/opcache.ini

RUN { \
    echo 'upload_max_filesize=32M'; \
    echo 'post_max_size=32M'; \
    echo 'max_execution_time=60'; \
    echo 'memory_limit=256M'; \
    echo 'display_errors=On'; \
    echo 'error_reporting=E_ALL'; \
    echo 'log_errors=On'; \
} > /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html
