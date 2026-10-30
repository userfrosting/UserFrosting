FROM node:24-bookworm

# Set working directory
WORKDIR /app

# Install system deps needed for build (native modules, less, etc.)
RUN apt-get update && apt-get install -y \
  bash \
  python3 \
  g++ \
  make \
  && rm -rf /var/lib/apt/lists/*

# Expose Vite dev port
EXPOSE 5173

# Add node_modules/.bin to PATH
ENV PATH=/app/node_modules/.bin:$PATH

# Bakery in the app container installs dependencies into the shared node_modules volume.
CMD echo "Waiting for Bakery to install frontend dependencies..."; \
    until [ -x /app/node_modules/.bin/vite ] && [ -f /app/node_modules/.package-lock.json ]; do sleep 5; done; \
    exec npm run vite:dev
