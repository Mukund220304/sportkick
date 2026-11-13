#!/usr/bin/env bash
set -e

PORT="${PORT:-8080}"

# If no external MySQL host is provided, start embedded MariaDB (MySQL) server inside the cloud container
if [ -z "${SPORTKICK_DB_HOST}" ] && [ -z "${MYSQLHOST}" ]; then
    echo "[SportKick Cloud] Starting internal MySQL/MariaDB service..."
    mkdir -p /run/mysqld
    chown -R mysql:mysql /run/mysqld /var/lib/mysql

    mariadbd --user=mysql --datadir=/var/lib/mysql --bind-address=127.0.0.1 --port=3306 &
    MYSQL_PID=$!

    # Wait for MySQL to accept connections
    for i in $(seq 1 30); do
        if mariadb-admin ping -h 127.0.0.1 --silent 2>/dev/null; then
            break
        fi
        sleep 1
    done

    echo "[SportKick Cloud] Initializing sportkick_db schema & seed data..."
    mariadb -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD(''); GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' IDENTIFIED BY '' WITH GRANT OPTION; FLUSH PRIVILEGES;" || true
    mariadb -h 127.0.0.1 -u root < /app/database/sportkick.sql
    echo "[SportKick Cloud] Database ready."
fi

echo "[SportKick Cloud] Starting PHP server on 0.0.0.0:${PORT}..."
exec php -S "0.0.0.0:${PORT}" -t /app
