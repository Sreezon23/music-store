FROM php:8.1-cli

WORKDIR /app

COPY . .

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --ignore-platform-reqs

EXPOSE 10000

CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]
