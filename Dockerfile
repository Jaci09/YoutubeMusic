FROM php:8.2-apache

# Instalar FFmpeg, Python, Pillow y AtomicParsley para metadatos/portadas
RUN apt-get update && apt-get install -y \
    ffmpeg \
    python3 \
    python3-pip \
    python3-pil \
    atomicparsley \
    curl \
    && curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp \
    && chmod a+rx /usr/local/bin/yt-dlp

COPY . /var/www/html/

RUN mkdir -p /var/www/html/temp_downloads && chmod -R 777 /var/www/html/temp_downloads

EXPOSE 80
