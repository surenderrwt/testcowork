<?php
/**
 * Product Filters Setup — TES-2
 * Run: php filters-setup.php (from WordPress root)
 * Purpose: Configure WooCommerce native filtering widgets & sidebar
 */

$_SERVER['HTTP_HOST']   = 'localhost:8081';
$_SERVER['REQUEST_URI'] = '/testcowork/';

require_once __DIR__ . '/wp-load.php';
wp_set_current_user(1);

echo "=== Filters Setup Script — TES-2 ===\n\n";

// ─────────────────────────────────────────────────
// Create a Filters sidebar widget area
// ─────────────────────────────────────────────────
echo "[Step 1] Registering Shop Sidebar...\n";

// The sidebar is registered in code; we configure widgets via options
$sidebars_widgets = get_option('sidebars_widgets', []);

// Make sure WooCommerce sidebar exists
if (!isset($sidebars_widgets['woocommerce_sidebar'])) {
    $sidebars_widgets['woocommerce_sidebar'] = [];
}

// ─────────────────────────────────────────────────
// Configure WooCommerce filter widgets
// ─────────────────────────────────────────────────
echo "[Step 2] Configuring filter widgets...\n";

// 1. Search widget
$search_id = 'woocommerce_product_search-' . rand(1000, 9999);
update_option('widget_woocommerce_product_search', [
    2 => ['title' => 'Search Products'],
]);

// 2. Category filter
update_option('widget_woocommerce_product_categories', [
    2 => [
        'title'              => 'Browse by Category',
        'orderby'            => 'name',
        'count'              => 1,
        'hierarchical'       => 1,
        'show_children_only' => 0,
        'hide_empty'         => 1,
        'max_depth'          => '',
    ],
]);

// 3. Price filter (slider)
update_option('widget_woocommerce_price_filter', [
    2 => ['title' => 'Filter by Price'],
]);

// 4. Size attribute filter
update_option('widget_woocommerce_layered_nav', [
    2 => [
        'title'         => 'Filter by Size',
        'attribute'     => 'pa_size',
        'display_type'  => 'list',
        'query_type'    => 'or',
    ],
    3 => [
        'title'         => 'Filter by Color',
        'attribute'     => 'pa_color',
        'display_type'  => 'list',
        'query_type'    => 'or',
    ],
    4 => [
        'title'         => 'Filter by Fabric',
        'attribute'     => 'pa_fabric',
        'display_type'  => 'list',
        'query_type'    => 'or',
    ],
    5 => [
        'title'         => 'Filter by Occasion',
        'attribute'     => 'pa_occasion',
        'display_type'  => 'list',
        'query_type'    => 'or',
    ],
]);

// 5. Active filters / current filters breadcrumb
update_option('widget_woocommerce_layered_nav_filters', [
    2 => ['title' => 'Active Filters'],
]);

// 6. Rating filter
update_option('widget_woocommerce_rating_filter', [
    2 => ['title' => 'Filter by Rating'],
]);

// Assign widgets to sidebar
$sidebars_widgets['woocommerce_sidebar'] = [
    'woocommerce_product_search-2',
    'woocommerce_layered_nav_filters-2',
    'woocommerce_product_categories-2',
    'woocommerce_price_filter-2',
    'woocommerce_layered_nav-2',    // Size
    'woocommerce_layered_nav-3',    // Color
    'woocommerce_layered_nav-4',    // Fabric
    'woocommerce_layered_nav-5',    // Occasion
    'woocommerce_rating_filter-2',
];

update_option('sidebars_widgets', $sidebars_widgets);
echo "  ✓ Filter widgets configured in WooCommerce sidebar\n";
echo "    - Search products\n";
echo "    - Active filters breadcrumb\n";
echo "    - Category (hierarchical)\n";
echo "    - Price range slider (₹0 – ₹25,000)\n";
echo "    - Size (layered nav)\n";
echo "    - Color (layered nav)\n";
echo "    - Fabric (layered nav)\n";
echo "    - Occasion (layered nav)\n";
echo "    - Rating filter\n";

// ─────────────────────────────────────────────────
// Sorting options (native WooCommerce)
// ─────────────────────────────────────────────────
echo "\n[Step 3] Configuring sort options...\n";

// WooCommerce already includes these sort options natively:
// Popularity (sales), Average Rating, Newest, Price Low→High, Price High→Low
// We just make sure they are enabled:
update_option('woocommerce_default_catalog_orderby', 'popularity');
update_option('woocommerce_catalog_orderby', [
    'menu_order' => 'Default sorting',
    'popularity' => 'Sort by popularity',
    'rating'     => 'Sort by average rating',
    'date'       => 'Sort by latest',
    'price'      => 'Sort by price: low to high',
    'price-desc' => 'Sort by price: high to low',
]);
echo "  ✓ Sorting options: Popularity, Rating, Newest, Price Low-High, Price High-Low\n";

// ─────────────────────────────────────────────────
// Per-page product count
// ─────────────────────────────────────────────────
update_option('woocommerce_catalog_columns', 3);
update_option('posts_per_page', 12); // 12 products per page
echo "  ✓ Shop grid: 3 columns, 12 products per page\n";

// ─────────────────────────────────────────────────
// Enable shop sidebar
// ─────────────────────────────────────────────────
update_option('woocommerce_sidebar_enabled', 'yes');

// Price filter range hint
update_option('ethnic_store_price_filter_max', 25000);

echo "\n✅ Filters setup complete!\n";
echo "   Sidebar with 8 filter widgets configured.\n\n";
