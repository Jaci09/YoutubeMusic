FROM php:8.2-apache

# Instalar FFmpeg, Python, Node.js y librerías necesarias
RUN apt-get update && apt-get install -y \
    ffmpeg \
    python3 \
    python3-pip \
    python3-pil \
    atomicparsley \
    nodejs \
    curl \
    && curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp \
    && chmod 777 /usr/local/bin/yt-dlp

COPY . /var/www/html/

# Asegurar permisos globales para la carpeta del servidor
RUN mkdir -p /var/www/html/temp_downloads && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 777 /var/www/html

EXPOSE 80
