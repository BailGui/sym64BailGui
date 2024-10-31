# Utiliser l'image de base PHP 8.1 avec fpm (FastCGI Process Manager) pour Alpine Linux 3.16
FROM php:8.1-fpm-alpine3.16
# installer les outils de base sans cache pour réduire l'image finale, avec des versions spécifiques :
RUN apk --no-cache add \
    bash \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip


# Définicer le répertoire de travail par défaut sur /usr/src/app.
WORKDIR /usr/src/app

# Copier les fichiers de configuration de Composer pour gérer les dépendances PHP.
COPY composer.json composer.lock ./

# Ajoute les répertoires bin et vendor/bin au PATH, facilitant l'accès aux commandes installées par Composer.
RUN PATH=$PATH:/usr/src/app/vendor/bin:bin

# Copier Composer depuis une image précédente pour éviter une réinstallation.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier tous les fichiers de l'application dans le répertoire de
COPY . .

# Installer les dépendances PHP spécifiées dans composer.json.
EXPOSE 9000
CMD ["php-fpm"]


