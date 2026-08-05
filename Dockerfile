FROM php:8.2-apache

# Instalar FFmpeg, Python y yt-dlp en el servidor Linux
RUN apt-get update && apt-get install -y \
    ffmpeg \
    python3 \
    python3-pip \
    curl \
    && curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp \
    && chmod a+rx /usr/local/bin/yt-dlp

# Copiar el proyecto al servidor
COPY . /var/www/html/

# Dar permisos a la carpeta de descargas
RUN mkdir -p /var/www/html/temp_downloads && chmod -R 777 /var/www/html/temp_downloads

EXPOSE 80