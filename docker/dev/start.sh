#!/usr/bin/env bash

set -euo pipefail

sync_npm_dependencies() {
    local project_dir="$1"
    local lock_file="${project_dir}/package-lock.json"
    local stamp_file="${project_dir}/node_modules/.package-lock.sha256"
    local lock_hash

    if [[ -f "${lock_file}" ]]; then
        lock_hash="$(sha256sum "${lock_file}" | cut -d ' ' -f 1)"

        if [[ ! -f "${stamp_file}" ]] || [[ "$(cat "${stamp_file}")" != "${lock_hash}" ]]; then
            echo "📦 Installing npm dependencies: ${project_dir}"
            npm --prefix "${project_dir}" install --no-audit
            printf '%s' "${lock_hash}" > "${stamp_file}"
        fi
    fi
}

echo "🚀 Preparing SaoLabs development container..."

sync_npm_dependencies /workspace/builder
sync_npm_dependencies /workspace/client
sync_npm_dependencies /workspace/saolabs

# Ensure local saolabs symlinks
mkdir -p /workspace/saolabs/node_modules/@saolabs
rm -rf /workspace/saolabs/node_modules/@saolabs/builder
ln -s /workspace/builder /workspace/saolabs/node_modules/@saolabs/builder
rm -rf /workspace/saolabs/node_modules/@saolabs/client
ln -s /workspace/client /workspace/saolabs/node_modules/@saolabs/client

cd /workspace/saolabs

echo "📦 Syncing Composer dependencies..."
composer install --no-interaction --prefer-dist

mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

echo "🔨 Building Saola views..."
npm run build:views

echo "👀 Starting SaoLabs dev server on port ${DEV_PORT:-8004}..."
exec npm run dev:container
