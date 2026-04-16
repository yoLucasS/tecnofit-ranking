FROM php:8.2-cli

# Instala extensoes PHP necessarias
RUN docker-php-ext-install pdo pdo_mysql

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretorio de trabalho
WORKDIR /app

# Copia os arquivos de dependencias primeiro (cache de camadas)
COPY composer.json composer.lock ./

# Instala as dependencias PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copia o restante do codigo
COPY . .

# Expoe a porta da aplicacao
EXPOSE 8080

# Comando padrao para iniciar o servidor PHP built-in
CMD ["php", "-S", "0.0.0.0:8080", "public/index.php"]
