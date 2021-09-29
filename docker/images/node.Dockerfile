FROM node:12.18-stretch

ARG HOST_USER_ID
ARG HOST_GROUP_ID

# change user so permissions can be the same as the user using docker
RUN set -xe && \
    groupmod -g ${HOST_GROUP_ID} node && \
    usermod -u ${HOST_USER_ID} -g ${HOST_GROUP_ID} node

USER node
