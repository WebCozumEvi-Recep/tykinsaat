#!/usr/bin/env bash
# Sunucuda çalıştırılır: depoyu günceller ve public_html'e yayar.
# Kullanım:  ./deploy.sh            (varsayılan: ../public_html)
#            ./deploy.sh /yol/public_html
set -euo pipefail

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TARGET="${1:-$(cd "$REPO_DIR/.." && pwd)/public_html}"

[ -d "$TARGET" ] || { echo "Hedef dizin yok: $TARGET"; exit 1; }

echo "→ Depo güncelleniyor: $REPO_DIR"
git -C "$REPO_DIR" pull --ff-only

echo "→ Yayınlanıyor: $TARGET"
rsync -a --delete \
  --exclude='.git' --exclude='.gitignore' --exclude='.github' \
  --exclude='*.sql' \
  --exclude='config.php' --exclude='config.ornek.php' \
  --exclude='deploy.sh' --exclude='DEPLOY.md' --exclude='README.md' \
  --exclude='.claude' --exclude='uploads' \
  "$REPO_DIR/" "$TARGET/"

mkdir -p "$TARGET/uploads"
chmod 775 "$TARGET/uploads"

echo "✓ Yayın tamam. Sürüm: $(git -C "$REPO_DIR" rev-parse --short HEAD)"
