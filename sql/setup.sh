#!/usr/bin/env bash

# Ensure we are in the correct dir
cd "$( dirname "${BASH_SOURCE[0]}" )" || exit

# Constants
DB_FILE='../data/db.sqlite'
SQL_FILES=('ddl' 'triggers' 'dml')

printf '%s\n' "Creating sqlite database in $DB_FILE"
rm -f "$DB_FILE" 2> /dev/null

for file in "${SQL_FILES[@]}"; do
  echo "Loading $file"
  sqlite3 "$DB_FILE" < "$file.sql"
done

printf '%s\n' 'Done'
