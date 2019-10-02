ARG OPENCART_VERSION
FROM bitnami/opencart:$OPENCART_VERSION

ARG OPENCART_VERSION

RUN apt update && apt install -y rsync

RUN apt-get update && apt-get install libicu-dev -y \
    && install -y libssl-dev \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl

RUN a2enmod rewrite
RUN a2enmod ssl
RUN service apache2 restart