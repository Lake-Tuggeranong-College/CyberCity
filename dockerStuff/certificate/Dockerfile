# Dockerfile

# Start with an Nginx image
FROM nginx:alpine

# Install OpenSSL for certificate generation
RUN apk add --no-cache openssl

# Set up directory for SSL certificates
RUN mkdir /etc/nginx/ssl

# Generate self-signed certificate with the hidden flag in the subjectAltName field
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/nginx/ssl/nginx.key \
    -out /etc/nginx/ssl/nginx.crt \
    -subj "/CN=CTF{ItsColdOutThereToday}" \
    -addext "subjectAltName=DNS:localhost"

# Copy over a default Nginx config file
COPY default.conf /etc/nginx/conf.d/default.conf

EXPOSE 443

CMD ["nginx", "-g", "daemon off;"]
