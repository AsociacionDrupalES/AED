ARG CLI_IMAGE
FROM ${CLI_IMAGE} AS cli

FROM uselagoon/nginx-drupal:latest

COPY --from=cli /app /app

ENV WEBROOT=web
