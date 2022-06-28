FROM php:8.1-fpm-bullseye
RUN apt update && apt install -y --no-install-recommends git zip unzip
RUN pecl zip
RUN docker-php-ext-install bcmath
RUN curl --silent --show-error https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer
ARG UNAME=invoicing-api
ARG UID=1000
ARG GID=1000
RUN groupadd -g $GID $UNAME
RUN useradd -m -u $UID -g $GID -o -s /bin/bash $UNAME
USER $UNAME
CMD bash -c "composer install && php-fpm"
