FROM bitnami/opencart:3.0.2.0-1

RUN apt update && apt install -y rsync
