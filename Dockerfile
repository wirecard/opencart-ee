FROM bitnami/opencart:3.0.3-1

RUN apt update && apt install -y rsync
