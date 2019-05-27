FROM bitnami/opencart:${OPENCART_VERSION}-1

RUN apt update && apt install -y rsync
