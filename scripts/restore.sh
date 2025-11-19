#!/bin/bash

###############################################################################
# DigiParc Disaster Recovery Script
# Restores database and files from backup
#
# Usage: ./scripts/restore.sh [backup_date]
# Example: ./scripts/restore.sh 2025-11-19
#
# If no date specified, uses latest backup
###############################################################################

set -e

# Configuration
BACKUP_DIR="$(dirname "$0")/../backups"
DB_HOST="${DB_HOST:-localhost}"
DB_NAME="${DB_NAME:-digiparc}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
UPLOADS_DIR="$(dirname "$0")/../public/uploads"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== DigiParc Disaster Recovery ===${NC}"
echo "Started at: $(date)"
echo ""

# Check if backup directory exists
if [ ! -d "$BACKUP_DIR" ]; then
    echo -e "${RED}Error: Backup directory not found: $BACKUP_DIR${NC}"
    exit 1
fi

# Find latest backups or use specified date
BACKUP_DATE="$1"

if [ -z "$BACKUP_DATE" ]; then
    echo "No date specified, finding latest backup..."
    DB_BACKUP=$(ls -t "$BACKUP_DIR"/db_*.sql.gz 2>/dev/null | head -1)
    FILES_BACKUP=$(ls -t "$BACKUP_DIR"/files_*.tar.gz 2>/dev/null | head -1)
else
    echo "Looking for backups from $BACKUP_DATE..."
    DB_BACKUP=$(ls -t "$BACKUP_DIR"/db_*${BACKUP_DATE}*.sql.gz 2>/dev/null | head -1)
    FILES_BACKUP=$(ls -t "$BACKUP_DIR"/files_*${BACKUP_DATE}*.tar.gz 2>/dev/null | head -1)
fi

# Check if backups found
if [ -z "$DB_BACKUP" ]; then
    echo -e "${RED}Error: No database backup found${NC}"
    exit 1
fi

echo -e "${GREEN}Found database backup:${NC} $(basename "$DB_BACKUP")"

if [ -n "$FILES_BACKUP" ]; then
    echo -e "${GREEN}Found files backup:${NC} $(basename "$FILES_BACKUP")"
fi

# Confirmation
echo ""
echo -e "${YELLOW}WARNING: This will replace current data!${NC}"
read -p "Are you sure you want to continue? (yes/no): " CONFIRM

if [ "$CONFIRM" != "yes" ]; then
    echo "Restore cancelled."
    exit 0
fi

# Restore database
echo ""
echo "Restoring database..."
if [ -n "$DB_PASS" ]; then
    gunzip < "$DB_BACKUP" | mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME"
else
    gunzip < "$DB_BACKUP" | mysql -h "$DB_HOST" -u "$DB_USER" "$DB_NAME"
fi

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Database restored successfully${NC}"
else
    echo -e "${RED}✗ Database restore failed${NC}"
    exit 1
fi

# Restore files
if [ -n "$FILES_BACKUP" ]; then
    echo ""
    echo "Restoring files..."

    # Backup current files first
    if [ -d "$UPLOADS_DIR" ]; then
        echo "Backing up current uploads..."
        mv "$UPLOADS_DIR" "${UPLOADS_DIR}.backup.$(date +%s)"
    fi

    # Create uploads directory
    mkdir -p "$UPLOADS_DIR"

    # Extract backup
    tar -xzf "$FILES_BACKUP" -C "$UPLOADS_DIR"

    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Files restored successfully${NC}"
    else
        echo -e "${RED}✗ Files restore failed${NC}"
        exit 1
    fi
fi

# Set permissions
echo ""
echo "Setting permissions..."
chmod -R 755 "$UPLOADS_DIR" 2>/dev/null || true

echo ""
echo -e "${GREEN}=== Restore Completed ===${NC}"
echo "Completed at: $(date)"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Verify application is working correctly"
echo "2. Check critical data integrity"
echo "3. Test user login and core features"
echo "4. Monitor logs for any errors"
