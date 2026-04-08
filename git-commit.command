#!/usr/bin/env bash
# Git commit & push — TES-2
cd /Users/surender-apple/Sites/testcowork

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Git Status"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
git status

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Staging files..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Stage setup scripts
git add setup-woocommerce.php
git add setup-products.php
git add payment-setup.php
git add filters-setup.php
git add seo-setup.php
git add checkout-setup.php
git add optimization-setup.php
git add run-setup.sh
git add run-setup.command
git add git-commit.command
git add STORE-ADMIN-GUIDE.md
git add .gitignore

# Stage child theme
git add wp-content/themes/testcowork-child/

# Stage .htaccess (performance rules added)
git add .htaccess 2>/dev/null || true

git status

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Committing..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
git commit -m "[TES-2] Convert WordPress blog to WooCommerce ethnic wear store

- Install & configure WooCommerce (INR currency, India, GST taxes)
- Shipping zones: India (flat ₹99, free >₹999, pickup) + International
- 8 product categories (Sarees, Suits, Lehengas, Kurtis, Men's Ethnic + sub)
- 4 global attributes: Size, Color, Fabric, Occasion
- 5 sample products with size/color variations
- Payment: Razorpay (test keys), COD 'Pay on Delivery', Bank Transfer
- Child theme: ethnic color palette, product grid, swatches, sticky cart
- Product filters: Category, Price, Size, Color, Fabric, Occasion
- SEO: Yoast, meta tags, Open Graph, XML sitemap, friendly URLs
- Checkout: guest checkout, GST field, WELCOME10/FREESHIP coupons
- Performance: WP Super Cache, GZIP, lazy load, browser caching
- Admin guide: STORE-ADMIN-GUIDE.md"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Pushing to origin main..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
git push origin main

echo ""
echo "✅ Git commit & push complete!"
echo "   Repo: https://github.com/surenderrwt/testcowork"
git log --oneline -3
echo ""
echo "Press any key to close..."
read -n 1
