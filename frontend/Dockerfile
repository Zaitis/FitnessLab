# Production image: static build served by a minimal nginx. This
# container's nginx only serves files and does SPA history fallback — TLS
# and the public hostnames are handled by the host's nginx (see
# deploy/nginx/), which reverse-proxies to this container.

FROM node:22-alpine AS build

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

# Vite env vars are compiled into the bundle at build time, not read at
# container startup — the API origin has to be baked in here.
ARG VITE_API_URL
ENV VITE_API_URL=${VITE_API_URL}

RUN npm run build

FROM nginx:1.27-alpine

COPY --from=build /app/dist /usr/share/nginx/html
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
