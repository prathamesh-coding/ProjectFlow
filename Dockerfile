# ──────────────────────────────────────────────────────────────────────────────
# Dockerfile for ProjectFlow – PHP 8.2 + Apache + PDO PostgreSQL
# Deployed on Render.com as a Web Service.
# ──────────────────────────────────────────────────────────────────────────────

FROM php:8.2-apache

# Install PostgreSQL client library and PDO drivers
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite (for clean URLs if needed later)
RUN a2enmod rewrite

# Copy app source into web root
COPY . /var/www/html/

# Allow .htaccess overrides
RUN sed -i 's/AllowOverride None/AllowOverride All/g' \
    /etc/apache2/apache2.conf

# Render dynamically assigns $PORT at runtime; patch Apache to use it
# The CMD below handles this at container startup.

EXPOSE 80

# At startup: swap hardcoded port 80 for Render's $PORT, then launch Apache
CMD ["/bin/bash", "-c", \
    "sed -i \"s/Listen 80/Listen ${PORT:-80}/g\" /etc/apache2/ports.conf && \
     sed -i \"s/:80>/:${PORT:-80}>/g\" /etc/apache2/sites-available/000-default.conf && \
     apache2-foreground"]
