FROM mmi3docker/symfony-2024

# Install curl library
RUN apt-get update && apt-get install -y \
    curl \
    libcurl4-openssl-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Force reinstall Stripe with curl support (ignore platform requirements)
WORKDIR /var/www
RUN composer require stripe/stripe-php --ignore-platform-req=ext-curl || true
