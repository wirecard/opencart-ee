ARG OPENCART_VERSION
FROM bitnami/opencart:$OPENCART_VERSION

ARG OPENCART_VERSION

RUN echo "Current opencart version -> $OPENCART_VERSION"
RUN apt update && apt install -y rsync
