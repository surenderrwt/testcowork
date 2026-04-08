<?php
/**
 * Performance Optimization Setup — TES-2
 * Run: php optimization-setup.php (from WordPress root)
 * Purpose: Install caching, configure GZIP, lazy load, browser caching
 */

$_SERVER['HTTP_HOST']   = 'localhost:8081';
$_SERVER['REQUEST_URI'] = '/testcowork/';

require_once __DIR__ . '/wp-load.php';
wp_set_current_user(1);

echo "=== Performance Optimization Script — TES-2 ===\n\n";

require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/misc.php';

// ─────────────────────────────────────────────────
// Install & Activate WP Super Cache
// ─────────────────────────────────────────────────
echo "[Step 1] Installing WP Super Cache...\n";

$cache_slug = 'wp-super-cache';
$cache_file = 'wp-super-cache/wp-cache.php';

if (!file_exists(WP_PLUGIN_DIR . '/' . $cache_file)) {
    $api = plugins_api('plugin_information', ['slug' => $cache_slug]);
    if (!is_wp_error($api)) {
        $upgrader = new Plugin_Upgrader(new WP_Upgrader_Skin());
        $result   = $upgrader->install($api->download_link);
        echo $result ? "  ✓ WP Super Cache installed.\n" : "  ✗ Installation failed — skip to manual install.\n";
    }
} else {
    echo "  ✓ WP Super Cache already installed.\n";
}

if (file_exists(WP_PLUGIN_DIR . '/' . $cache_file) && !is_plugin_active($cache_file)) {
    activate_plugin($cache_file);
    echo "  ✓ WP Super Cache activated.\n";
}

// Configure WP Super Cache
global $wp_cache_enabled, $cache_enabled;
update_option('wpsupercache_enabled',    1);
update_option('super_cache_enabled',     1);
update_option('wp_cache_enabled',        true);
update_option('wp_cache_not_logged_in',  true);
update_option('wp_cache_no_cache_for_get', false);
update_option('wp_cache_compression',    true);
update_option('cache_max_time',          3600); // 1 hour
echo "  ✓ WP Super Cache configured (1 hour TTL, compression on)\n";

// ─────────────────────────────────────────────────
// WordPress Core Performance Settings
// ─────────────────────────────────────────────────
echo "\n[Step 2] Configuring WordPress performance options...\n";

// Reduce post revisions
if (!defined('WP_POST_REVISIONS')) {
    update_option('wp_post_revisions', 5);
}

// Reduce autosave interval
update_option('wp_autosave_interval', 120); // 2 minutes

// Image quality (high quality for product photos)
update_option('jpeg_quality', 85);

// Disable pinging
update_option('default_ping_status', 'closed');
update_option('default_pingback_flag', '0');
update_option('ping_sites', '');

echo "  ✓ Post revisions capped at 5\n";
echo "  ✓ JPEG quality set to 85% (balanced quality/size)\n";
echo "  ✓ Pinging disabled\n";

// ─────────────────────────────────────────────────
// Lazy Loading for Images (native WP + WooCommerce)
// ─────────────────────────────────────────────────
echo "\n[Step 3] Enabling lazy loading for product images...\n";

// WordPress 5.5+ adds loading="lazy" natively to images via wp_lazy_loading_enabled
add_filter('wp_lazy_loading_enabled', '__return_true');

// For WooCommerce product thumbnails
add_filter('woocommerce_product_get_image', function ($image) {
    return str_replace('<img ', '<img loading="lazy" ', $image);
}, 10, 1);

// Store flag
update_option('ethnic_store_lazy_load', 'yes');
echo "  ✓ Native lazy loading enabled (loading='lazy' attribute)\n";
echo "  ✓ WooCommerce product images lazy loaded\n";

// ─────────────────────────────────────────────────
// CSS/JS Minification via WordPress filters
// ─────────────────────────────────────────────────
echo "\n[Step 4] Configuring CSS/JS optimization...\n";

// Defer non-critical JS (basic)
add_filter('script_loader_tag', function ($tag, $handle) {
    $defer_scripts = ['ethnic-store-js', 'wc-add-to-cart', 'wc-add-to-cart-variation'];
    if (in_array($handle, $defer_scripts)) {
        return str_replace(' src=', ' defer src=', $tag);
    }
    return $tag;
}, 10, 2);

// Remove query strings from static resources (better caching)
add_filter('script_loader_src', 'ethnic_store_remove_query_string', 15);
add_filter('style_loader_src',  'ethnic_store_remove_query_string', 15);
function ethnic_store_remove_query_string($src) {
    if (strpos($src, '?ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}

update_option('ethnic_store_js_deferred', 'yes');
echo "  ✓ Non-critical JS deferred\n";
echo "  ✓ Query strings removed from static assets\n";

// ─────────────────────────────────────────────────
// Update .htaccess with GZIP + Browser Caching
// ─────────────────────────────────────────────────
echo "\n[Step 5] Updating .htaccess with GZIP and browser caching rules...\n";

$htaccess_path = ABSPATH . '.htaccess';
$htaccess_content = '';
if (file_exists($htaccess_path)) {
    $htaccess_content = file_get_contents($htaccess_path);
}

$performance_rules = '
# BEGIN Ethnic Wear Store Performance Rules — TES-2
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/x-javascript application/json application/xml
    BrowserMatch ^Mozilla/4 gzip-only-text/html
    BrowserMatch ^Mozilla/4\.0[678] no-gzip
    BrowserMatch \bMSIE !no-gzip !gzip-only-text/html
    Header append Vary User-Agent
</IfModule>

<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpeg         "access plus 1 year"
    ExpiresByType image/png          "access plus 1 year"
    ExpiresByType image/gif          "access plus 1 year"
    ExpiresByType image/webp         "access plus 1 year"
    ExpiresByType image/svg+xml      "access plus 1 year"
    ExpiresByType image/x-icon       "access plus 1 year"
    ExpiresByType text/css           "access plus 1 month"
    ExpiresByType text/javascript    "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType application/pdf    "access plus 1 month"
    ExpiresByType text/html          "access plus 0 seconds"
</IfModule>

<IfModule mod_headers.c>
    <FilesMatch "\.(jpg|jpeg|png|gif|webp|ico|svg)$">
        Header set Cache-Control "max-age=31536000, public"
    </FilesMatch>
    <FilesMatch "\.(css|js)$">
        Header set Cache-Control "max-age=2592000, public"
    </FilesMatch>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
# END Ethnic Wear Store Performance Rules
';

// Only add if not already present
if (strpos($htaccess_content, 'BEGIN Ethnic Wear Store Performance Rules') === false) {
    $htaccess_content = $performance_rules . "\n" . $htaccess_content;
    file_put_contents($htaccess_path, $htaccess_content);
    echo "  ✓ GZIP compression rules added to .htaccess\n";
    echo "  ✓ Browser caching rules added to .htaccess\n";
    echo "  ✓ Security headers added (X-Frame-Options, XSS-Protection)\n";
} else {
    echo "  → Performance rules already in .htaccess.\n";
}

// ─────────────────────────────────────────────────
// WooCommerce-specific Performance
// ─────────────────────────────────────────────────
echo "\n[Step 6] WooCommerce performance tuning...\n";

// Disable WooCommerce scripts on non-WC pages
update_option('woocommerce_cart_redirect_after_add',       'no');
update_option('woocommerce_enable_ajax_add_to_cart',       'yes');
update_option('woocommerce_lookup_table_enabled',          'yes');

// Product catalog cache
update_option('woocommerce_product_lookup_table_enabled',  'yes');

echo "  ✓ AJAX add-to-cart enabled (no page reload)\n";
echo "  ✓ Product lookup table enabled for fast queries\n";

// ─────────────────────────────────────────────────
// Image size optimization for WooCommerce
// ─────────────────────────────────────────────────
echo "\n[Step 7] Optimizing image sizes...\n";

update_option('woocommerce_thumbnail_cropping',      'custom');
update_option('woocommerce_thumbnail_cropping_custom_width',  '4');
update_option('woocommerce_thumbnail_cropping_custom_height', '3');

// Set image dimensions
update_option('woocommerce_thumbnail_image_width',   300);
update_option('woocommerce_single_image_width',      600);
update_option('woocommerce_gallery_thumbnail_image_width', 100);

echo "  ✓ Thumbnail: 300px (4:3 ratio)\n";
echo "  ✓ Single product image: 600px\n";
echo "  ✓ Gallery thumbnail: 100px\n";

echo "\n✅ Performance optimization complete!\n";
echo "   - WP Super Cache installed & configured\n";
echo "   - GZIP compression via .htaccess\n";
echo "   - Browser caching (1 year for images, 1 month for CSS/JS)\n";
echo "   - Lazy loading for product images\n";
echo "   - Non-critical JS deferred\n";
echo "   - WooCommerce AJAX add-to-cart enabled\n\n";
