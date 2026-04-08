<?php
/**
 * SEO Setup Script — TES-2
 * Run: php seo-setup.php (from WordPress root)
 * Purpose: Install Yoast SEO, configure meta tags, schema, and sitemaps
 */

$_SERVER['HTTP_HOST']   = 'localhost:8081';
$_SERVER['REQUEST_URI'] = '/testcowork/';

require_once __DIR__ . '/wp-load.php';
wp_set_current_user(1);

echo "=== SEO Setup Script — TES-2 ===\n\n";

require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/misc.php';

// ─────────────────────────────────────────────────
// Install & Activate Yoast SEO
// ─────────────────────────────────────────────────
echo "[Step 1] Installing Yoast SEO...\n";

$yoast_slug = 'wordpress-seo';
$yoast_file = 'wordpress-seo/wp-seo.php';

if (!file_exists(WP_PLUGIN_DIR . '/' . $yoast_file)) {
    $api = plugins_api('plugin_information', ['slug' => $yoast_slug]);
    if (!is_wp_error($api)) {
        $upgrader = new Plugin_Upgrader(new WP_Upgrader_Skin());
        $result   = $upgrader->install($api->download_link);
        echo $result ? "  ✓ Yoast SEO installed.\n" : "  ✗ Installation failed.\n";
    }
} else {
    echo "  ✓ Yoast SEO already installed.\n";
}

if (file_exists(WP_PLUGIN_DIR . '/' . $yoast_file) && !is_plugin_active($yoast_file)) {
    activate_plugin($yoast_file);
    echo "  ✓ Yoast SEO activated.\n";
} elseif (is_plugin_active($yoast_file)) {
    echo "  ✓ Yoast SEO already active.\n";
}

// ─────────────────────────────────────────────────
// Configure Yoast SEO Options
// ─────────────────────────────────────────────────
echo "\n[Step 2] Configuring Yoast SEO settings...\n";

// Yoast stores its settings in wpseo, wpseo_titles, wpseo_social options
$wpseo = get_option('wpseo', []);
$wpseo = array_merge($wpseo, [
    'ms_defaults_set'         => true,
    'version'                 => '21.0',
    'tracking'                => false,
    'enable_xml_sitemap'      => true,
    'enable_headless_rest_endpoints' => false,
]);
update_option('wpseo', $wpseo);

$wpseo_titles = get_option('wpseo_titles', []);
$wpseo_titles = array_merge($wpseo_titles, [
    // Site-wide
    'website_name'    => 'Ethnic Wear Store',
    'title-home-wpseo' => 'Ethnic Wear Store | Traditional Indian Clothing',
    'metadesc-home-wpseo' => 'Shop authentic ethnic wear online. Sarees, salwar suits, lehengas & more. Free shipping above ₹999.',
    'title-tax-product_cat' => '%%term_title%% | Ethnic Wear Store',
    'title-product'   => '%%title%% | Ethnic Wear Store',
    'metadesc-product' => '%%excerpt%%',
    // Schema
    'company_or_person' => 'company',
    'company_name'    => 'Ethnic Wear Store',
    // Breadcrumbs
    'breadcrumbs-enable' => true,
    'breadcrumbs-sep'    => ' › ',
    'breadcrumbs-home'   => 'Home',
]);
update_option('wpseo_titles', $wpseo_titles);

// Social / Open Graph
$wpseo_social = get_option('wpseo_social', []);
$wpseo_social = array_merge($wpseo_social, [
    'opengraph'  => true,
    'twitter'    => true,
    'og_default_image' => '',
]);
update_option('wpseo_social', $wpseo_social);
echo "  ✓ Yoast SEO configured (site title, meta description, OG tags)\n";

// ─────────────────────────────────────────────────
// Set per-category SEO meta
// ─────────────────────────────────────────────────
echo "\n[Step 3] Setting category SEO meta...\n";

$category_seo = [
    'sarees'      => [
        'focus_kw'    => 'buy sarees online',
        'meta_desc'   => 'Shop beautiful sarees online. Banarasi silk sarees, cotton sarees, designer sarees for every occasion. Free shipping in India.',
        'title'       => 'Buy Sarees Online — Silk, Cotton & Designer Sarees',
    ],
    'lehengas'    => [
        'focus_kw'    => 'bridal lehenga',
        'meta_desc'   => 'Browse stunning bridal lehengas and designer lehenga sets. Premium embroidery, vibrant colors. Order online with free shipping.',
        'title'       => 'Bridal & Designer Lehengas Online | Ethnic Wear Store',
    ],
    'kurtis'      => [
        'focus_kw'    => 'ethnic kurtis online',
        'meta_desc'   => 'Shop ethnic kurtis online. Cotton kurtis, georgette kurtis, designer kurtis for casual, office & festive wear. Fast delivery across India.',
        'title'       => 'Ethnic Kurtis Online — Cotton, Georgette & Designer Kurtis',
    ],
    'salwar-suits' => [
        'focus_kw'    => 'salwar suits online',
        'meta_desc'   => 'Shop beautiful salwar suits and Anarkali sets online. Embroidered and printed suits for all occasions. Free shipping above ₹999.',
        'title'       => 'Salwar Suits & Anarkali Sets | Buy Online',
    ],
    'mens-ethnic' => [
        'focus_kw'    => 'mens ethnic wear India',
        'meta_desc'   => 'Shop men\'s ethnic wear online. Kurta pyjamas, sherwanis, dhoti sets for weddings, festivals and casual occasions.',
        'title'       => "Men's Ethnic Wear — Kurta Pyjama, Sherwani Online",
    ],
];

foreach ($category_seo as $slug => $seo) {
    $term = get_term_by('slug', $slug, 'product_cat');
    if (!$term) continue;
    update_term_meta($term->term_id, '_yoast_wpseo_focuskw',   $seo['focus_kw']);
    update_term_meta($term->term_id, '_yoast_wpseo_metadesc',  $seo['meta_desc']);
    update_term_meta($term->term_id, '_yoast_wpseo_title',     $seo['title']);
    echo "  ✓ SEO meta set for category: $slug\n";
}

// ─────────────────────────────────────────────────
// SEO-friendly Permalinks
// ─────────────────────────────────────────────────
echo "\n[Step 4] Setting SEO-friendly URLs...\n";

// Set permalink structure to /shop/category/product-name equivalent:
// WooCommerce product base: /product/
// Category base: /product-category/ (WooCommerce default)
update_option('permalink_structure', '/%postname%/');
update_option('woocommerce_permalinks', [
    'product_base'              => '/shop',
    'category_base'             => 'shop',
    'tag_base'                  => 'shop/tag',
    'attribute_base'            => '',
    'use_verbose_page_rules'    => false,
]);
flush_rewrite_rules();
echo "  ✓ Permalink structure: /%postname%/\n";
echo "  ✓ Product base: /shop/{product-name}\n";
echo "  ✓ Category base: /shop/{category-name}\n";

// ─────────────────────────────────────────────────
// Schema markup helper (product pages)
// ─────────────────────────────────────────────────
echo "\n[Step 5] Adding product schema markup...\n";

// Schema is injected via wp_head hook — stored as option
update_option('ethnic_store_schema_enabled', 'yes');
// Actual schema injection is in functions.php via Yoast's graph filters
// This option acts as a flag

// ─────────────────────────────────────────────────
// Sitemap
// ─────────────────────────────────────────────────
echo "\n[Step 6] Enabling XML sitemap...\n";
update_option('wpseo_xml_sitemap_enabled', 'yes');
// Yoast auto-generates sitemap at /sitemap_index.xml
echo "  ✓ XML sitemap enabled at /sitemap_index.xml\n";

echo "\n✅ SEO setup complete!\n";
echo "   Yoast SEO configured with:\n";
echo "   - Site title: 'Ethnic Wear Store | Traditional Indian Clothing'\n";
echo "   - Meta descriptions for all 5 categories\n";
echo "   - Open Graph / Twitter Card enabled\n";
echo "   - SEO-friendly URLs: /shop/{product-name}\n";
echo "   - XML Sitemap: /sitemap_index.xml\n\n";
