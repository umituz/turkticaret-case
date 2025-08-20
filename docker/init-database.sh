#!/bin/bash
# docker/init-database.sh

set -e
set -u

echo "Turkticaret database initialization started"

# Create database for turkticaret
if [ -n "$POSTGRES_USER" ]; then
    echo "Creating turkticaret_api database..."
    
    psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
        CREATE DATABASE turkticaret_api;
        GRANT ALL PRIVILEGES ON DATABASE turkticaret_api TO "$POSTGRES_USER";
EOSQL
    
    echo "Turkticaret database created successfully!"
else
    echo "POSTGRES_USER is not set"
fi

echo "Database initialization completed."