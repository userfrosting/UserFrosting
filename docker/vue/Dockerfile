FROM node:24-bookworm

ARG NODE_UID=1000
ARG NODE_GID=1000

# Set working directory
WORKDIR /app

# Install system deps needed for build (native modules, less, etc.)
RUN apt-get update && apt-get install -y \
  bash \
  python3 \
  g++ \
  make \
  && rm -rf /var/lib/apt/lists/*

RUN groupmod --non-unique --gid "${NODE_GID}" node \
  && usermod --non-unique --uid "${NODE_UID}" --gid "${NODE_GID}" node \
  && mkdir -p /app/node_modules \
  && chown node:node /app/node_modules

USER node

# Expose Vite dev port
EXPOSE 5173

# Add node_modules/.bin to PATH
ENV PATH=/app/node_modules/.bin:$PATH

# Bakery in the app container installs dependencies into the shared node_modules volume.
CMD echo "Waiting for Bakery to install frontend dependencies..."; \
    until [ -x /app/node_modules/.bin/vite ] && [ -f /app/node_modules/.package-lock.json ]; do sleep 5; done; \
    exec npm run vite:dev
