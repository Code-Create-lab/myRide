#!/bin/bash

# === CONFIGURATION ===
PHP_VERSION="php8.3"   # 🔁 Change this as needed (php8.0, php8.1, etc.)
TARGET_DIR="$PWD"      # 🗂️  Current directory (run this from your project root)

echo "📦 Installing composer.phar into: $TARGET_DIR"
echo "🔧 Using PHP version: $PHP_VERSION"

# Step 1: Download Composer installer
curl -sS https://getcomposer.org/installer -o composer-setup.php

# Step 2: Verify the installer (optional but recommended)
HASH=$(curl -sS https://composer.github.io/installer.sig)
ACTUAL_HASH=$(sha384sum composer-setup.php | awk '{ print $1 }')

if [ "$HASH" != "$ACTUAL_HASH" ]; then
    echo "❌ Hash mismatch! Installer corrupt."
    rm composer-setup.php
    exit 1
fi

# Step 3: Install composer.phar using selected PHP version
$PHP_VERSION composer-setup.php --install-dir="$TARGET_DIR" --filename="composer.phar"

# Step 4: Clean up
rm composer-setup.php

# Step 5: Success Message
echo "✅ Composer installed at: $TARGET_DIR/composer.phar"
echo "💡 Run with: $PHP_VERSION $TARGET_DIR/composer.phar"
