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

# Install dependencies from the mounted project before running Vite.
CMD ["sh", "-c", "npm install && exec npx vite"]
