FROM mmi3docker/symfony-2024

# Installe toutes les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    php8.3-mysql \
    php8.3-pdo \
    php8.3-pdo-mysql \
    php8.3-xml \
    php8.3-dom \
    && rm -rf /var/lib/apt/lists/*

# Installe Composer dependencies avec workaround cURL
RUN composer require stripe/stripe-php --ignore-platform-req=ext-curl || true

RUN echo "upload_max_filesize = 100M" >> /etc/php/8.3/cli/php.ini && \
    echo "post_max_size = 100M" >> /etc/php/8.3/cli/php.ini && \
    echo "max_execution_time = 300" >> /etc/php/8.3/cli/php.ini && \
    echo "memory_limit = 512M" >> /etc/php/8.3/cli/php.ini

WORKDIR /var/www