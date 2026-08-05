FROM php:8.2-apache

# Instalar FFmpeg, Python, Pillow y AtomicParsley
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

# Crear carpeta temporal y asignar permisos de lectura/escritura a Apache (www-data)
RUN mkdir -p /var/www/html/temp_downloads && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 777 /var/www/html

EXPOSE 80
