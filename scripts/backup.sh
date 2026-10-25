#!/usr/bin/env bash
set -euo pipefail
umask 077

backup_dir="${GARMENTOS_BACKUP_DIR:-/srv/backups/garmentos}"
database_name="${GARMENTOS_DB_NAME:-garmentsos}"
credentials_file="${GARMENTOS_MYSQL_CREDENTIALS:-/etc/garmentos/backup.cnf}"
mkdir -p "$backup_dir"
backup_file="$backup_dir/garmentos-$(date -u +%Y%m%dT%H%M%SZ).sql.gz"
mysqldump --defaults-extra-file="$credentials_file" \
  --single-transaction --quick --routines --triggers --events \
  --no-tablespaces --set-gtid-purged=OFF "$database_name" | gzip > "$backup_file"
gzip --test "$backup_file"
printf 'Backup created: %s\n' "$backup_file"
