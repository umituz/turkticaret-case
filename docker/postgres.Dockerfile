# docker/postgres.Dockerfile
FROM postgres:15

COPY docker/init-database.sh /docker-entrypoint-initdb.d/
RUN chmod +x /docker-entrypoint-initdb.d/init-database.sh