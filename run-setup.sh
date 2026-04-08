#!/usr/bin/env bash
# ═══════════════════════════════════════════════════════════
#  Ethnic Wear Store — Master Setup Script
#  TES-2: Convert WordPress Blog to WooCommerce Store
#  Run: bash run-setup.sh (from WordPress root directory)
# ═══════════════════════════════════════════════════════════

set -e
cd "$(dirname "$0")"

echo ""
echo "╔══════════════════════════════════════════════════╗"
echo "║   Ethnic Wear Store — Full WooCommerce Setup     ║"
echo "║   Jira: TES-2                                    ║"
echo "╚══════════════════════════════════════════════════╝"
echo ""

# Prefer XAMPP PHP (configured for XAMPP MySQL socket) over Homebrew PHP
XAMPP_PHP="/Applications/XAMPP/xamppfiles/bin/php"
if [ -x "$XAMPP_PHP" ]; then
    PHP="$XAMPP_PHP"
    echo "  Using XAMPP PHP: $PHP"
else
    PHP=$(which php 2>/dev/null || echo "/usr/bin/php")
    echo "  Using system PHP: $PHP"
fi

run_script() {
    local script=$1
    local label=$2
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "  ▶ $label"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    $PHP "$script"
    echo ""
}

# Step 1: WooCommerce core setup
run_script "setup-woocommerce.php"  "Step 1/7 — WooCommerce Installation & Configuration"

# Step 2: Product categories, attributes, and sample products
run_script "setup-products.php"     "Step 2/7 — Product Structure & Sample Products"

# Step 3: Payment gateways
run_script "payment-setup.php"      "Step 3/7 — Payment Gateway Setup"

# Step 4: Product filters
run_script "filters-setup.php"      "Step 4/7 — Product Filters"

# Step 5: SEO
run_script "seo-setup.php"          "Step 5/7 — SEO Configuration"

# Step 6: Checkout optimization
run_script "checkout-setup.php"     "Step 6/7 — Cart & Checkout Optimization"

# Step 7: Performance
run_script "optimization-setup.php" "Step 7/7 — Performance Optimization"

echo ""
echo "╔══════════════════════════════════════════════════╗"
echo "║  ✅  ALL SETUP SCRIPTS COMPLETE                  ║"
echo "╠══════════════════════════════════════════════════╣"
echo "║  Store URL  : http://localhost:8081/testcowork/shop  ║"
echo "║  Admin URL  : http://localhost:8081/testcowork/wp-admin ║"
echo "║                                                  ║"
echo "║  Next Steps:                                     ║"
echo "║  1. Activate child theme in WP Admin > Themes    ║"
echo "║  2. Replace Razorpay test keys before go-live    ║"
echo "║  3. Add real bank details for NEFT/RTGS transfer ║"
echo "║  4. Upload product images in WP Admin            ║"
echo "╚══════════════════════════════════════════════════╝"
echo ""
