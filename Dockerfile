ARG OPENCART_VERSION=${OPENCART_VERSION}
FROM bitnami/opencart:${OPENCART_VERSION}

RUN apt update && apt install -y rsync
