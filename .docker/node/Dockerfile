FROM node:23-alpine

WORKDIR /var/www/minimaxi

RUN apk update && apk add zsh

CMD ["tail", "-f", "/dev/null"]

COPY . .
