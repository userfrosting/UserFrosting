FROM nginx:alpine

# Remove default site config
RUN rm /etc/nginx/conf.d/default.conf

# Copy in your custom nginx.conf (http block) and server block
COPY nginx.conf /etc/nginx/nginx.conf
COPY default.conf /etc/nginx/conf.d/default.conf