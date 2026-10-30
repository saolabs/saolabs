#!/usr/bin/env bash
# Deploy a committed static build, keeping a production backup.
set -euo pipefail
cd "$(dirname "$0")/.."
[[ -f .deploy.env ]] && source .deploy.env
: "${DEPLOY_HOST:?Create .deploy.env from .deploy.env.example}"
: "${DEPLOY_DEST:?Set DEPLOY_DEST in .deploy.env}"
[[ "$DEPLOY_DEST" =~ ^/[A-Za-z0-9_./-]+$ && "$DEPLOY_DEST" != / && "$DEPLOY_DEST" != /var/www ]] || { echo 'Invalid deployment directory' >&2; exit 1; }
[[ -z "$(git status --porcelain)" ]] || { echo 'Commit all website changes before deployment' >&2; exit 1; }
git pull --ff-only --quiet
REV="$(git rev-parse --short HEAD)"
echo "Deploying SaoLabs commit $REV"
npm run build
php artisan test --compact
npm run dist:static

BACKUP_DIR="$(dirname "$DEPLOY_DEST")/.saolabs-backups"
BACKUP="$BACKUP_DIR/$(date -u +%Y%m%dT%H%M%SZ)-$REV.tar.gz"
ssh -o BatchMode=yes -o ConnectTimeout=15 "$DEPLOY_HOST" "test -d '$DEPLOY_DEST' && mkdir -p '$BACKUP_DIR' && tar -C '$DEPLOY_DEST' -czf '$BACKUP' . && test -s '$BACKUP'"
echo "Production backup: $BACKUP"
rsync -az --delay-updates --delete --exclude saola-dist.json --exclude '/.well-known/' dist-static/web/ "$DEPLOY_HOST:$DEPLOY_DEST/"
ssh "$DEPLOY_HOST" "chown -R www-data:www-data '$DEPLOY_DEST'"

failed=0
while IFS= read -r url; do
    code="$(curl -sS --max-time 20 -L -o /dev/null -w '%{http_code}' "$url")"
    echo "$code $url"
    [[ "$code" == 200 ]] || failed=1
done < <(python3 - <<'PY'
import xml.etree.ElementTree as ET
for node in ET.parse('dist-static/web/sitemap.xml').getroot():
    print(node.find('{http://www.sitemaps.org/schemas/sitemap/0.9}loc').text)
PY
)
((failed == 0)) || { echo 'Production URL verification failed' >&2; exit 1; }
echo "https://saolabs.org is running commit $REV"
