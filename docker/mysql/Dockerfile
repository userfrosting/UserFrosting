

# Use MySQL 9.4.0 as the base
FROM mysql:9.4.0

# Apply custom configuration for Docker compatibility
COPY mysql.conf /etc/mysql/conf.d/custom.cnf

# Expose MySQL default port
EXPOSE 3306

# Default authentication plugin
CMD ["mysqld"]
