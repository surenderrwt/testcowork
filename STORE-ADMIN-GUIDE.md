# Ethnic Wear Store — Admin Guide
> **Jira:** TES-2 | **Store URL:** http://localhost:8081/testcowork/shop | **Admin URL:** http://localhost:8081/testcowork/wp-admin

---

## Table of Contents
1. [How to Add a New Product](#1-how-to-add-a-new-product)
2. [How to Manage Orders](#2-how-to-manage-orders)
3. [How to Add/Edit Categories](#3-how-to-addedit-categories)
4. [How to Run Promotions & Coupons](#4-how-to-run-promotions--coupons)
5. [Payment Gateway Live Key Setup](#5-payment-gateway-live-key-setup)
6. [Shipping Zone Management](#6-shipping-zone-management)
7. [Inventory Management Tips](#7-inventory-management-tips)
8. [Theme & Design Customization](#8-theme--design-customization)
9. [SEO Management](#9-seo-management)
10. [Performance & Caching](#10-performance--caching)

---

## 1. How to Add a New Product

### Step 1 — Go to Products
- Log in to WP Admin → **Products** → **Add New**

### Step 2 — Fill Product Details
| Field | Where to find it | Example |
|-------|-----------------|---------|
| Product name | Top text box | "Banarasi Silk Saree" |
| Description | Large text area | Full product description |
| Short description | Scroll down | 1-2 sentence summary |
| Price (Regular) | Product Data → General | 4999 |
| Price (Sale) | Product Data → General | 3999 |
| SKU | Product Data → Inventory | BNRS-001 |
| Stock quantity | Product Data → Inventory | 50 |

### Step 3 — Set Product Type
- **Simple product** — single item, no size/color variations (e.g., plain saree)
- **Variable product** — has size/color/fabric options (most ethnic wear)

For variable products:
1. Change type dropdown to **Variable product**
2. Go to **Attributes** tab → select `pa_size`, `pa_color`, click **Add**
3. Check **Used for variations** for each attribute
4. Go to **Variations** tab → click **Generate all variations** → set price/stock per variation

### Step 4 — Assign Category
- Right panel → **Product categories** → tick the correct category (Sarees, Kurtis, etc.)

### Step 5 — Upload Product Image
- Right panel → **Product image** → click **Set product image**
- Upload main photo (recommended: 800×800px minimum, square crop)
- Add extra photos via **Product gallery** (3–8 photos recommended)

### Step 6 — Add Attributes (for Simple Products)
- Product Data → **Attributes** tab
- Click **Add** → choose attribute (pa_color, pa_fabric, etc.)
- Enter values separated by `|` (e.g., `Red | Gold | Maroon`)
- Check **Visible on the product page**

### Step 7 — SEO (Yoast)
- Scroll down to **Yoast SEO** section
- Fill **SEO title** (e.g., "Buy Banarasi Silk Saree Online | Ethnic Wear Store")
- Fill **Meta description** (150–160 chars)
- Set **Focus keyphrase** (e.g., "Banarasi silk saree")

### Step 8 — Publish
- Click the **Publish** button (or **Schedule** for future date)

---

## 2. How to Manage Orders

### View All Orders
WP Admin → **WooCommerce** → **Orders**

### Order Statuses
| Status | Meaning | Action Required |
|--------|---------|-----------------|
| Pending payment | Payment not yet confirmed | Wait or contact customer |
| Processing | Payment received | Prepare & ship |
| On hold | Awaiting manual payment (COD/NEFT) | Confirm receipt then mark Processing |
| Completed | Shipped & delivered | No action |
| Cancelled | Order cancelled | Restock inventory |
| Refunded | Money returned | No action |

### Processing an Order
1. Click the order number
2. Review customer details, items, and payment method
3. Add tracking number in **Order notes** (e.g., "Shipped via India Post. Tracking: INXXXXXXXX")
4. Change status to **Completed** → click **Update**
5. Customer automatically receives confirmation email

### Bulk Actions
- Select multiple orders via checkboxes
- Use **Bulk actions** dropdown → Change status / Export

### Export Orders
- WP Admin → **WooCommerce** → **Reports** → **Orders**
- Or use WooCommerce export plugin for CSV export

---

## 3. How to Add/Edit Categories

### Add a New Category
1. WP Admin → **Products** → **Categories**
2. Fill in:
   - **Name** (e.g., "Dupattas")
   - **Slug** (e.g., `dupattas` — auto-generated, can edit)
   - **Parent category** (for sub-categories, e.g., parent = "Men's Ethnic")
   - **Description** (shown on category page)
3. Click **Add New Product Category**

### Edit Existing Category
1. Hover over category name → click **Edit**
2. Change name, description, or image
3. To add a **category image**: click on the image placeholder → upload/select
4. Click **Update**

### Current Category Structure
```
Sarees
Salwar Suits
Lehengas
Kurtis
Men's Ethnic
  ├── Sherwanis
  ├── Kurta Pyjama
  └── Dhoti Sets
```

---

## 4. How to Run Promotions & Coupons

### Create a Coupon
1. WP Admin → **WooCommerce** → **Coupons** → **Add coupon**
2. Set coupon code (e.g., `DIWALI20`) — customers type this at checkout
3. Configure in **General** tab:
   - **Discount type**: Percentage / Fixed cart / Fixed product / Free shipping
   - **Coupon amount**: e.g., `20` for 20%
   - **Coupon expiry date**: optional
4. Configure in **Usage restriction** tab:
   - **Minimum spend**: e.g., `999`
   - **Individual use only**: tick if cannot be combined with other coupons
5. Configure in **Usage limits** tab:
   - **Usage limit per coupon**: e.g., `100` (total redemptions)
   - **Usage limit per user**: e.g., `1` (once per customer)
6. Click **Publish**

### Existing Coupons
| Code | Type | Value | Minimum | Limit |
|------|------|-------|---------|-------|
| `WELCOME10` | 10% off | 10% | None | 1 per customer |
| `FREESHIP` | Free shipping | Free shipping | ₹500 | Unlimited |

### WooCommerce Sale Prices
For product-level discounts:
1. Go to **Products** → edit product
2. In **General** tab: set **Sale price** and optionally tick the calendar icon for scheduled dates
3. Products with sale price automatically show the "SALE" badge

---

## 5. Payment Gateway Live Key Setup

> ⚠️ **IMPORTANT:** Do this before going live. Currently using test/placeholder keys.

### Razorpay Live Keys
1. Sign up / log in at [dashboard.razorpay.com](https://dashboard.razorpay.com)
2. Go to **Settings** → **API Keys** → **Generate Live Mode Key**
3. Copy your **Key ID** and **Key Secret**
4. In WP Admin: **WooCommerce** → **Settings** → **Payments** → **Razorpay** → **Manage**
5. Replace:
   - `rzp_test_placeholder` → your live Key ID (starts with `rzp_live_`)
   - `placeholder_secret` → your live Key Secret
6. Change mode from **Test** to **Live**
7. Save changes

### Bank Transfer (NEFT/RTGS) — Update Details
1. WP Admin → **WooCommerce** → **Settings** → **Payments** → **Direct bank transfer** → **Manage**
2. Update the account details section with your real bank account:
   - Account Name
   - Account Number
   - Bank Name
   - IFSC Code
3. Save changes

### Cash on Delivery
No changes needed — works out of the box. To restrict to specific shipping zones:
1. WP Admin → **WooCommerce** → **Settings** → **Payments** → **Cash on delivery** → **Manage**
2. Under **Enable for shipping methods**: select only the India zone methods

---

## 6. Shipping Zone Management

### View Shipping Zones
WP Admin → **WooCommerce** → **Settings** → **Shipping**

### Current Zones
| Zone | Region | Methods |
|------|--------|---------|
| India | India (IN) | Standard Delivery ₹99, Free Shipping >₹999, Local Pickup |
| International | All other countries | Disabled flat rate |

### Add a New Shipping Method to India Zone
1. Click **India** zone → **Add shipping method**
2. Select method type → **Add shipping method**
3. Click the method name to configure its title and cost

### Edit Flat Rate Cost
1. Click **India** zone
2. Click **Standard Delivery** (flat rate) → edit icon
3. Change **Cost** field (e.g., from `99` to `49` for sale period)
4. Save

### Temporarily Offer Free Shipping to All
1. Click **India** zone → click **Free Shipping** → edit
2. Change **Free shipping requires...** to "A valid free shipping coupon" or "A minimum order amount"
3. To make it unconditional: set **Minimum order amount** to `0`

### Add State-Specific Rates (e.g., higher cost for NE states)
1. Create a new zone → name it "Northeast India"
2. Add states: Assam, Meghalaya, Mizoram, Manipur, Nagaland, Tripura, Sikkim, Arunachal Pradesh
3. Add flat rate with higher cost (e.g., ₹199)

---

## 7. Inventory Management Tips

### Set Low Stock Alert
1. WP Admin → **WooCommerce** → **Settings** → **Products** → **Inventory**
2. Set **Low stock threshold** to `5` (get notified when stock drops to 5)
3. Notifications go to your store email

### Bulk Update Stock
1. WP Admin → **Products**
2. Select multiple products → **Bulk actions** → **Edit** → **Apply**
3. Update **Stock** in the bulk editor panel

### View Low Stock Products
WP Admin → **WooCommerce** → **Reports** → **Stock** → **Low in stock**

### Out-of-Stock Products
- Products with 0 stock are automatically marked "Out of stock"
- To hide them from shop: WP Admin → **WooCommerce** → **Settings** → **Products** → tick **"Hide out of stock items from the catalog"**

### Track Variation Stock
For variable products (e.g., Anarkali Suit — size S, Blue):
1. Open product → **Variations** tab
2. Expand each variation → tick **Manage stock?** → set quantity
3. WooCommerce tracks each size/color combination independently

### Inventory Report
WP Admin → **WooCommerce** → **Reports** → **Stock** — shows overview of all stock levels

---

## 8. Theme & Design Customization

### Child Theme Location
```
wp-content/themes/testcowork-child/
├── style.css          — All custom CSS
├── functions.php      — PHP hooks, shortcodes, widget registrations
└── assets/
    └── js/
        └── ethnic-store.js  — Swatches, size guide, sticky cart
```

### Activate Child Theme
WP Admin → **Appearance** → **Themes** → hover over **Testcowork Child** → **Activate**

### Edit Colors
Open `wp-content/themes/testcowork-child/style.css` and update the CSS variables at the top:
```css
:root {
    --color-primary:    #8B0000;  /* Deep Maroon */
    --color-secondary:  #DAA520;  /* Golden */
    --color-accent:     #FFF8DC;  /* Cream */
    --color-background: #FFFAF0;  /* Floral White */
}
```

### Homepage Shortcodes
Add these shortcodes to any page in WP Admin → Pages → Edit:
```
[ethnic_hero]           — Hero banner
[offer_banner]          — Free shipping offer strip
[ethnic_categories]     — Category grid (4 columns)
[new_arrivals count=6]  — Latest 6 products
[new_arrivals count=6 heading="Bestsellers"]  — with custom heading
```

### Homepage Setup
1. WP Admin → **Pages** → **Add New** → title: "Home"
2. Add shortcodes above in desired order
3. WP Admin → **Settings** → **Reading** → set "Your homepage displays" to "A static page" → select "Home"

---

## 9. SEO Management

### Per-Product SEO (Yoast)
Each product page has a **Yoast SEO** panel at the bottom:
- **SEO title**: Keep under 60 characters, include main keyword
- **Meta description**: 150–160 characters, include keyword + call to action
- **Focus keyphrase**: The main keyword you want to rank for
- The **traffic light indicator** shows green = good, orange = needs improvement

### Sitemap
Auto-generated at: `http://localhost:8081/testcowork/sitemap_index.xml`
Submit to Google Search Console after going live.

### URL Structure
Products: `/shop/product-name/`
Categories: `/shop/category-name/`
These are set via WP Admin → **Settings** → **Permalinks** → custom `/shop/%postname%/`

---

## 10. Performance & Caching

### Clear Cache After Changes
After updating products, prices, or content — clear WP Super Cache:
WP Admin → **Settings** → **WP Super Cache** → **Delete Cache**

### Or via WP-CLI
```bash
cd /Users/surender-apple/Sites/testcowork
wp cache flush
wp super-cache flush
```

### Image Optimization Tips
- Use WebP format when possible (better compression)
- Recommended product image size: 800×800px
- WooCommerce auto-generates thumbnails — after bulk upload, run:
  `wp media regenerate --yes`

### Performance Checklist
- [ ] WP Super Cache enabled
- [ ] GZIP compression active (check via browser DevTools → Network → Response Headers → `Content-Encoding: gzip`)
- [ ] Images lazy-loaded (check source code for `loading="lazy"`)
- [ ] CSS/JS files cached (Cache-Control header on static files)

---

## Quick Reference — WP Admin URLs

| Task | URL |
|------|-----|
| All Products | /wp-admin/edit.php?post_type=product |
| Add Product | /wp-admin/post-new.php?post_type=product |
| Orders | /wp-admin/edit.php?post_type=shop_order |
| Categories | /wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product |
| Coupons | /wp-admin/edit.php?post_type=shop_coupon |
| WooCommerce Settings | /wp-admin/admin.php?page=wc-settings |
| Shipping | /wp-admin/admin.php?page=wc-settings&tab=shipping |
| Payments | /wp-admin/admin.php?page=wc-settings&tab=checkout |
| Reports | /wp-admin/admin.php?page=wc-reports |
| Appearance/Themes | /wp-admin/themes.php |
| Plugins | /wp-admin/plugins.php |

---

*Last updated: 2026-04-09 | TES-2 | Ethnic Wear Store*
