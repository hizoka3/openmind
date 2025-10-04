#!/bin/bash
# build-deploy.sh - OpenMind WordPress Plugin Builder

set -e

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}🚀 OpenMind Plugin Build${NC}"
echo "====================================="

# Get current version and increment
current_version=$(grep "Version:" openmind.php | sed 's/.*Version: *//' | sed 's/ *\*.*$//' | tr -d '\n\r')
IFS='.' read -ra VERSION_PARTS <<< "$current_version"
major=${VERSION_PARTS[0]:-1}
minor=${VERSION_PARTS[1]:-0}
patch=${VERSION_PARTS[2]:-0}
new_patch=$((patch + 1))
new_version="$major.$minor.$new_patch"

echo -e "${BLUE}Current version: $current_version${NC}"
echo -e "${BLUE}Next version: $new_version${NC}"
echo ""
echo "This will create: releases/openmind-v${new_version}.zip"
read -p "Continue? (y/N): " -r
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Cancelled."
    exit 0
fi

echo ""
echo -e "${YELLOW}[1/5]${NC} Updating version numbers..."

# Update version in files (macOS compatible)
if [[ "$OSTYPE" == "darwin"* ]]; then
    sed -i '' "s/Version: .*/Version: $new_version/" openmind.php
    sed -i '' "s/define('OPENMIND_VERSION', '[^']*')/define('OPENMIND_VERSION', '$new_version')/" openmind.php
    [ -f "package.json" ] && sed -i '' "s/\"version\": \"[^\"]*\"/\"version\": \"$new_version\"/" package.json
else
    sed -i "s/Version: .*/Version: $new_version/" openmind.php
    sed -i "s/define('OPENMIND_VERSION', '[^']*')/define('OPENMIND_VERSION', '$new_version')/" openmind.php
    [ -f "package.json" ] && sed -i "s/\"version\": \"[^\"]*\"/\"version\": \"$new_version\"/" package.json
fi

echo -e "${GREEN}✅ Version updated to $new_version${NC}"

echo -e "${YELLOW}[2/5]${NC} Installing dependencies..."
rm -rf node_modules vendor
composer install --no-dev --optimize-autoloader --quiet >/dev/null 2>&1
npm ci --silent >/dev/null 2>&1
echo -e "${GREEN}✅ Dependencies installed${NC}"

echo -e "${YELLOW}[3/5]${NC} Building assets..."
npm run build:css --silent >/dev/null 2>&1
echo -e "${GREEN}✅ CSS compiled with Tailwind${NC}"

echo -e "${YELLOW}[4/5]${NC} Creating ZIP with correct structure..."

# Create releases directory
mkdir -p releases

# ZIP file path
zip_file="releases/openmind-v${new_version}.zip"

# Remove existing ZIP if exists
rm -f "$zip_file"

# Create a temporary directory for the plugin structure
temp_parent="temp_build_$$"
plugin_dir="$temp_parent/openmind"

# Create the plugin directory structure
mkdir -p "$plugin_dir"

# Copy all necessary files to the plugin directory
cp openmind.php "$plugin_dir/"
cp README.md "$plugin_dir/"
cp test-setup.php "$plugin_dir/"
cp -r src "$plugin_dir/"
cp -r vendor "$plugin_dir/"
cp -r assets "$plugin_dir/"
cp -r templates "$plugin_dir/"
cp composer.json "$plugin_dir/"

# Clean development files from the plugin directory
cd "$plugin_dir"

# Remove specific development/test files (keeping test-setup.php)
rm -f create-test-messages.php 2>/dev/null || true
rm -f flatten.sh 2>/dev/null || true
rm -f estructura.txt 2>/dev/null || true
rm -f build-deploy.sh 2>/dev/null || true

# Remove source CSS files (keep only compiled)
rm -rf assets/css/src/ 2>/dev/null || true

# Remove map files and other dev artifacts
find . -name "*.map" -delete 2>/dev/null || true
find . -name "*.scss" -delete 2>/dev/null || true
find . -name ".DS_Store" -delete 2>/dev/null || true

# Clean vendor directory
find vendor/ -name "*.md" -delete 2>/dev/null || true
find vendor/ -name "tests" -type d -exec rm -rf {} + 2>/dev/null || true
find vendor/ -name "test" -type d -exec rm -rf {} + 2>/dev/null || true
find vendor/ -name "Tests" -type d -exec rm -rf {} + 2>/dev/null || true
find vendor/ -name "Test" -type d -exec rm -rf {} + 2>/dev/null || true
find vendor/ -name ".git" -type d -exec rm -rf {} + 2>/dev/null || true

cd ../..

# Create the ZIP from the temp parent directory (includes openmind/ folder)
cd "$temp_parent"
zip -r "../$zip_file" openmind >/dev/null 2>&1
cd ..

# Clean up
rm -rf "$temp_parent"

echo -e "${GREEN}✅ ZIP created: $zip_file${NC}"

echo -e "${YELLOW}[5/5]${NC} Verifying ZIP structure..."
echo "ZIP contents (first 15 files):"
unzip -l "$zip_file" | head -20

# Test extraction to verify structure
test_dir="test_verify_$$"
mkdir "$test_dir"
cd "$test_dir"
unzip -q "../$zip_file"

if [ -d "openmind" ] && [ -f "openmind/openmind.php" ]; then
    echo -e "${GREEN}✅ ZIP structure is correct!${NC}"
    echo -e "${GREEN}✅ WordPress will detect this plugin properly${NC}"

    # Verify key files
    echo ""
    echo "Key files verified:"
    [ -f "openmind/openmind.php" ] && echo "  ✅ openmind.php"
    [ -f "openmind/test-setup.php" ] && echo "  ✅ test-setup.php (included)"
    [ -d "openmind/src" ] && echo "  ✅ src/"
    [ -d "openmind/vendor" ] && echo "  ✅ vendor/"
    [ -d "openmind/assets" ] && echo "  ✅ assets/"
    [ -d "openmind/templates" ] && echo "  ✅ templates/"
    [ -f "openmind/README.md" ] && echo "  ✅ README.md"
    [ -f "openmind/composer.json" ] && echo "  ✅ composer.json"

    # Check what was cleaned
    echo ""
    echo "Development files removed:"
    [ ! -f "openmind/create-test-messages.php" ] && echo "  ✅ create-test-messages.php (removed)"
    [ ! -d "openmind/assets/css/src" ] && echo "  ✅ CSS source files (removed)"
    [ ! -d "openmind/node_modules" ] && echo "  ✅ node_modules/ (not included)"
else
    echo -e "${RED}❌ ZIP structure is wrong${NC}"
    echo "Found:"
    ls -la
fi

cd ..
rm -rf "$test_dir"

# Show final information
zip_size=$(du -sh "$zip_file" | cut -f1)
echo ""
echo "================================="
echo -e "${GREEN}🎉 BUILD COMPLETE!${NC}"
echo "================================="
echo ""
echo "📦 Package: $zip_file"
echo "📏 Size: $zip_size"
echo "🔖 Version: $new_version"
echo "🚀 Ready for WordPress!"
echo ""
echo "To install:"
echo "1. Upload via WordPress admin (Plugins → Add New → Upload)"
echo "2. Or extract to wp-content/plugins/ (will create openmind/ folder)"
echo ""
echo "⚠️  Note: test-setup.php is included for easy testing"
echo ""
echo "Next steps:"
echo "- Test the plugin on a fresh WordPress install"
echo "- Commit version changes: git add openmind.php package.json"
echo "- Create release: git commit -m 'Release v${new_version}'"
echo ""