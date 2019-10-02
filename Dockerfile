ARG OPENCART_VERSION
FROM bitnami/opencart:$OPENCART_VERSION

ARG OPENCART_VERSION

RUN apt update && apt install -y rsync libssl-dev openssl ssl-cert
