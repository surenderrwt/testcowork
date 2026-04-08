<?php
/**
 * WooCommerce Setup Script — TES-2
 * Run: php setup-woocommerce.php (from WordPress root)
 * Purpose: Install, activate, and configure WooCommerce for Indian ethnic wear store
 */

define('ABSPATH_SETUP', true);

// Bootstrap WordPress
$_SERVER['HTTP_HOST']   = 'localhost:8081';
$_SERVER['REQUEST_URI'] = '/testcowork/';

require_once __DIR__ . '/wp-load.php';

if (!current_user_can('manage_options')) {
    // Run as admin
    wp_set_current_user(1);
}

echo "=== WooCommerce Setup Script — TES-2 ===\n\n";

// ─────────────────────────────────────────────────
// 2a. Install & Activate WooCommerce
// ─────────────────────────────────────────────────
echo "[Step 1] Installing & Activating WooCommerce...\n";

require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/misc.php';

$woo_slug = 'woocommerce';
$woo_file = 'woocommerce/woocommerce.php';

if (!file_exists(WP_PLUGIN_DIR . '/' . $woo_file)) {
    echo "  → WooCommerce not found. Installing from WordPress.org...\n";

    $api = plugins_api('plugin_information', [
        'slug'   => $woo_slug,
        'fields' => ['short_description' => false, 'sections' => false, 'requires' => false,
                     'rating' => false, 'ratings' => false, 'downloaded' => false,
                     'last_updated' => false, 'added' => false, 'tags' => false,
                     'compatibility' => false, 'homepage' => false, 'donate_link' => false],
    ]);

    if (is_wp_error($api)) {
        echo "  ✗ Plugin API error: " . $api->get_error_message() . "\n";
        exit(1);
    }

    $upgrader = new Plugin_Upgrader(new WP_Upgrader_Skin());
    $result   = $upgrader->install($api->download_link);

    if (is_wp_error($result) || !$result) {
        echo "  ✗ Installation failed.\n";
        exit(1);
    }
    echo "  ✓ WooCommerce installed.\n";
} else {
    echo "  ✓ WooCommerce already installed.\n";
}

if (!is_plugin_active($woo_file)) {
    $activated = activate_plugin($woo_file);
    if (is_wp_error($activated)) {
        echo "  ✗ Activation failed: " . $activated->get_error_message() . "\n";
        exit(1);
    }
    echo "  ✓ WooCommerce activated.\n";
} else {
    echo "  ✓ WooCommerce already active.\n";
}

// Reload WooCommerce class
if (!class_exists('WooCommerce')) {
    require_once WP_PLUGIN_DIR . '/woocommerce/woocommerce.php';
}

// ─────────────────────────────────────────────────
// 2b. Base Settings
// ─────────────────────────────────────────────────
echo "\n[Step 2] Configuring base WooCommerce settings...\n";

$base_settings = [
    'woocommerce_currency'               => 'INR',
    'woocommerce_currency_pos'           => 'left',
    'woocommerce_price_thousand_sep'     => ',',
    'woocommerce_price_decimal_sep'      => '.',
    'woocommerce_price_num_decimals'     => 2,
    'woocommerce_default_country'        => 'IN:MH',   // India, Maharashtra
    'woocommerce_store_address'          => 'Store Address, Mumbai',
    'woocommerce_store_city'             => 'Mumbai',
    'woocommerce_default_customer_address' => 'base',
    'woocommerce_weight_unit'            => 'kg',
    'woocommerce_dimension_unit'         => 'cm',
    'woocommerce_manage_stock'           => 'yes',
    'woocommerce_notify_low_stock'       => 'yes',
    'woocommerce_notify_no_stock'        => 'yes',
    'woocommerce_low_stock_amount'       => 5,
    'woocommerce_reviews_enabled'        => 'yes',
    'woocommerce_enable_guest_checkout'  => 'yes',
    'woocommerce_enable_checkout_login_reminder' => 'yes',
    'woocommerce_enable_signup_and_login_from_checkout' => 'yes',
    'woocommerce_registration_generate_password' => 'yes',
    'woocommerce_calc_taxes'             => 'yes',
    'woocommerce_prices_include_tax'     => 'no',
    'woocommerce_tax_based_on'           => 'shipping',
    'woocommerce_shipping_tax_class'     => 'inherit',
    'woocommerce_tax_round_at_subtotal'  => 'no',
    'woocommerce_tax_display_shop'       => 'excl',
    'woocommerce_tax_display_cart'       => 'incl',
    'woocommerce_tax_total_display'      => 'itemized',
];

foreach ($base_settings as $key => $value) {
    update_option($key, $value);
}
echo "  ✓ Currency set to INR (₹)\n";
echo "  ✓ Country set to India\n";
echo "  ✓ Weight: kg, Dimensions: cm\n";
echo "  ✓ Tax enabled (GST for India)\n";

// ─────────────────────────────────────────────────
// 2b-ii. GST Tax Classes & Rates
// ─────────────────────────────────────────────────
echo "\n[Step 3] Setting up GST tax rates...\n";

global $wpdb;

// Add standard tax class for GST
$tax_classes = WC_Tax::get_tax_classes();
if (!in_array('GST 5%', $tax_classes)) {
    $existing  = get_option('woocommerce_tax_classes', '');
    $new_class = $existing . "\nGST 5%\nGST 12%";
    update_option('woocommerce_tax_classes', trim($new_class));
    echo "  ✓ GST 5% and GST 12% tax classes created.\n";
}

// Clear existing rates for a clean setup
$wpdb->query("DELETE FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_country = 'IN'");
$wpdb->query("DELETE FROM {$wpdb->prefix}woocommerce_tax_rate_locations WHERE 1=1");

// Standard rate — GST 5% (clothing under ₹1000)
$wpdb->insert(
    $wpdb->prefix . 'woocommerce_tax_rates',
    [
        'tax_rate_country'  => 'IN',
        'tax_rate_state'    => '',
        'tax_rate'          => '5.0000',
        'tax_rate_name'     => 'GST 5% (clothing < ₹1000)',
        'tax_rate_priority' => 1,
        'tax_rate_compound' => 0,
        'tax_rate_shipping' => 0,
        'tax_rate_order'    => 1,
        'tax_rate_class'    => 'gst-5',
    ]
);

// Reduced rate — GST 12% (clothing over ₹1000)
$wpdb->insert(
    $wpdb->prefix . 'woocommerce_tax_rates',
    [
        'tax_rate_country'  => 'IN',
        'tax_rate_state'    => '',
        'tax_rate'          => '12.0000',
        'tax_rate_name'     => 'GST 12% (clothing > ₹1000)',
        'tax_rate_priority' => 1,
        'tax_rate_compound' => 0,
        'tax_rate_shipping' => 0,
        'tax_rate_order'    => 2,
        'tax_rate_class'    => 'gst-12',
    ]
);
echo "  ✓ GST 5% rate set for clothing under ₹1000\n";
echo "  ✓ GST 12% rate set for clothing over ₹1000\n";

// ─────────────────────────────────────────────────
// 2c. Shipping Zones
// ─────────────────────────────────────────────────
echo "\n[Step 4] Configuring shipping zones...\n";

// Delete existing zones (except the Rest of World zone = 0)
$existing_zones = WC_Shipping_Zones::get_zones();
foreach ($existing_zones as $zone) {
    if ($zone['zone_id'] > 0) {
        $z = new WC_Shipping_Zone($zone['zone_id']);
        $z->delete();
    }
}

// Zone 1: India
$india_zone = new WC_Shipping_Zone();
$india_zone->set_zone_name('India');
$india_zone->set_zone_order(1);
$india_zone->save();
$india_zone->add_location('IN', 'country');

// Helper: save shipping method instance settings via WP options (WC 8.x+)
function save_shipping_instance($method_id, $instance_id, $settings) {
    $option_key = "woocommerce_{$method_id}_{$instance_id}_settings";
    $existing   = get_option($option_key, []);
    update_option($option_key, array_merge($existing, $settings));
}

// Flat rate ₹99
$flat_rate_id = $india_zone->add_shipping_method('flat_rate');
save_shipping_instance('flat_rate', $flat_rate_id, [
    'title'   => 'Standard Delivery',
    'cost'    => '99',
    'enabled' => 'yes',
]);

// Free shipping above ₹999
$free_ship_id = $india_zone->add_shipping_method('free_shipping');
save_shipping_instance('free_shipping', $free_ship_id, [
    'title'      => 'Free Shipping (above ₹999)',
    'min_amount' => '999',
    'requires'   => 'min_amount',
    'enabled'    => 'yes',
]);

// Local pickup
$pickup_id = $india_zone->add_shipping_method('local_pickup');
save_shipping_instance('local_pickup', $pickup_id, [
    'title'   => 'Local Pickup',
    'cost'    => '0',
    'enabled' => 'yes',
]);
echo "  ✓ India shipping zone created\n";
echo "  ✓ Flat rate ₹99 standard delivery\n";
echo "  ✓ Free shipping threshold ₹999+\n";
echo "  ✓ Local pickup added\n";

// Zone 2: International (disabled)
$intl_zone = new WC_Shipping_Zone();
$intl_zone->set_zone_name('International');
$intl_zone->set_zone_order(2);
$intl_zone->save();
$intl_flat_id = $intl_zone->add_shipping_method('flat_rate');
save_shipping_instance('flat_rate', $intl_flat_id, [
    'title'   => 'International Shipping',
    'cost'    => '1500',
    'enabled' => 'no',
]);
echo "  ✓ International zone created (disabled by default)\n";

// Mark WooCommerce setup complete
update_option('woocommerce_onboarding_profile', ['completed' => true]);
update_option('woocommerce_task_list_hidden', 'yes');

// Flush rewrite rules
flush_rewrite_rules();

echo "\n✅ WooCommerce setup complete!\n";
echo "   Store URL: http://localhost:8081/testcowork/shop\n\n";
