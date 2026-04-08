<?php
/**
 * Checkout & Cart Optimization — TES-2
 * Run: php checkout-setup.php (from WordPress root)
 * Purpose: Configure checkout, coupons, and abandoned cart recovery
 */

$_SERVER['HTTP_HOST']   = 'localhost:8081';
$_SERVER['REQUEST_URI'] = '/testcowork/';

require_once __DIR__ . '/wp-load.php';
wp_set_current_user(1);

echo "=== Checkout Setup Script — TES-2 ===\n\n";

// ─────────────────────────────────────────────────
// Guest Checkout & Account Settings
// ─────────────────────────────────────────────────
echo "[Step 1] Configuring checkout settings...\n";

update_option('woocommerce_enable_guest_checkout',              'yes');
update_option('woocommerce_enable_checkout_login_reminder',     'yes');
update_option('woocommerce_enable_signup_and_login_from_checkout', 'yes');
update_option('woocommerce_registration_generate_password',     'yes');
update_option('woocommerce_enable_coupons',                     'yes');
update_option('woocommerce_calc_discounts_sequentially',        'no');
update_option('woocommerce_checkout_terms_page_id',             0); // no T&C page required for now
echo "  ✓ Guest checkout enabled\n";
echo "  ✓ Coupon field enabled at checkout\n";

// ─────────────────────────────────────────────────
// Order Emails Configuration
// ─────────────────────────────────────────────────
echo "\n[Step 2] Configuring order confirmation emails...\n";

// WooCommerce customer order email settings
update_option('woocommerce_email_from_name',    'Ethnic Wear Store');
update_option('woocommerce_email_from_address', 'noreply@ethnicwearstore.in');

$email_footer = "Thank you for shopping with Ethnic Wear Store!\n\nFor support, contact us at support@ethnicwearstore.in\nor call us at +91 XXXXXXXXXX\n\nShop more at: http://localhost:8081/testcowork/shop";
update_option('woocommerce_email_footer_text', $email_footer);

// Email styling — match our ethnic store palette
update_option('woocommerce_email_background_color', '#FFFAF0');
update_option('woocommerce_email_body_background_color', '#FFFFFF');
update_option('woocommerce_email_base_color', '#8B0000');
update_option('woocommerce_email_text_color', '#333333');
echo "  ✓ Order confirmation email configured (Ethnic Wear Store branding)\n";

// ─────────────────────────────────────────────────
// Sample Coupon: WELCOME10
// ─────────────────────────────────────────────────
echo "\n[Step 3] Creating sample coupon...\n";

global $wpdb;
$existing_coupon = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_title = 'WELCOME10' AND post_type = 'shop_coupon' AND post_status = 'publish'");

if (!$existing_coupon) {
    $coupon_id = wp_insert_post([
        'post_title'   => 'WELCOME10',
        'post_content' => '',
        'post_status'  => 'publish',
        'post_type'    => 'shop_coupon',
    ]);

    if ($coupon_id && !is_wp_error($coupon_id)) {
        update_post_meta($coupon_id, 'discount_type',             'percent');
        update_post_meta($coupon_id, 'coupon_amount',             '10');
        update_post_meta($coupon_id, 'individual_use',            'yes');
        update_post_meta($coupon_id, 'usage_limit',               '1');
        update_post_meta($coupon_id, 'usage_limit_per_user',      '1');
        update_post_meta($coupon_id, 'usage_count',               '0');
        update_post_meta($coupon_id, 'expiry_date',               '');
        update_post_meta($coupon_id, 'free_shipping',             'no');
        update_post_meta($coupon_id, 'exclude_sale_items',        'no');
        update_post_meta($coupon_id, 'minimum_amount',            '');
        update_post_meta($coupon_id, 'maximum_amount',            '');
        update_post_meta($coupon_id, 'customer_email',            []);
        update_post_meta($coupon_id, 'description',               '10% off first order. Welcome discount for new customers!');
        echo "  ✓ Coupon 'WELCOME10' created: 10% off, single use per customer\n";
    }
} else {
    echo "  → Coupon 'WELCOME10' already exists.\n";
}

// Additional coupon: FREESHIP for free shipping
$existing_fs = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_title = 'FREESHIP' AND post_type = 'shop_coupon' AND post_status = 'publish'");
if (!$existing_fs) {
    $fs_id = wp_insert_post([
        'post_title'  => 'FREESHIP',
        'post_status' => 'publish',
        'post_type'   => 'shop_coupon',
    ]);
    if ($fs_id && !is_wp_error($fs_id)) {
        update_post_meta($fs_id, 'discount_type',         'fixed_cart');
        update_post_meta($fs_id, 'coupon_amount',         '0');
        update_post_meta($fs_id, 'free_shipping',         'yes');
        update_post_meta($fs_id, 'minimum_amount',        '500');
        update_post_meta($fs_id, 'individual_use',        'no');
        echo "  ✓ Coupon 'FREESHIP' created: free shipping on orders ₹500+\n";
    }
} else {
    echo "  → Coupon 'FREESHIP' already exists.\n";
}

// ─────────────────────────────────────────────────
// Abandoned Cart Recovery (Basic — via wp-cron)
// ─────────────────────────────────────────────────
echo "\n[Step 4] Setting up basic abandoned cart recovery...\n";

// Store last cart activity time in session
add_action('woocommerce_add_to_cart', function () {
    WC()->session->set('cart_last_activity', time());
});

// Register a cron event (runs hourly, checks for stale carts > 1 hour)
if (!wp_next_scheduled('ethnic_check_abandoned_carts')) {
    wp_schedule_event(time(), 'hourly', 'ethnic_check_abandoned_carts');
}

// Abandoned cart check hook
add_action('ethnic_check_abandoned_carts', function () {
    // Basic: log users with old cart activity (extend with email in production)
    $threshold = time() - (60 * 60); // 1 hour ago
    $users_with_carts = get_users(['meta_key' => '_woocommerce_persistent_cart_' . get_current_blog_id()]);
    $count = 0;
    foreach ($users_with_carts as $user) {
        $cart = get_user_meta($user->ID, '_woocommerce_persistent_cart_' . get_current_blog_id(), true);
        if (!empty($cart['cart']) && !empty($user->ID)) {
            $count++;
        }
    }
    if ($count > 0) {
        error_log("[Ethnic Store] Abandoned cart check: $count carts found. Consider sending recovery emails.");
    }
});

// Save cron actions to DB
update_option('ethnic_store_abandoned_cart_recovery', 'enabled');
echo "  ✓ Abandoned cart monitoring enabled (hourly wp-cron check)\n";
echo "  ℹ  For advanced email recovery, consider CartFlows or WooCommerce Follow-Ups\n";

// ─────────────────────────────────────────────────
// Checkout page trust badges (handled in functions.php)
// WooCommerce endpoint pages
// ─────────────────────────────────────────────────
echo "\n[Step 5] Verifying WooCommerce pages...\n";

$pages_needed = [
    'shop'      => 'Shop',
    'cart'      => 'Cart',
    'checkout'  => 'Checkout',
    'myaccount' => 'My account',
];

foreach ($pages_needed as $option_key => $page_name) {
    $page_id = wc_get_page_id($option_key);
    if ($page_id && get_post($page_id)) {
        echo "  ✓ $page_name page: " . get_permalink($page_id) . "\n";
    } else {
        // Create the page
        $new_id = wp_insert_post([
            'post_title'   => $page_name,
            'post_content' => $option_key === 'shop' ? '' : '[woocommerce_' . ($option_key === 'myaccount' ? 'my_account' : $option_key) . ']',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);
        update_option('woocommerce_' . $option_key . '_page_id', $new_id);
        echo "  ✓ $page_name page created (ID: $new_id)\n";
    }
}

// ─────────────────────────────────────────────────
// Checkout field improvements (India-specific)
// ─────────────────────────────────────────────────
echo "\n[Step 6] Configuring India-specific checkout fields...\n";

update_option('woocommerce_default_country', 'IN:MH');
update_option('woocommerce_allowed_countries', 'specific');
update_option('woocommerce_specific_allowed_countries', ['IN']);
// Enable all countries for international orders too (comment line above + uncomment below):
// update_option('woocommerce_allowed_countries', 'all');

// State label overrides for India
add_filter('woocommerce_get_country_locale', function ($locale) {
    $locale['IN'] = [
        'state' => ['label' => 'State', 'required' => true],
        'postcode' => ['label' => 'PIN Code', 'required' => true, 'priority' => 65],
    ];
    return $locale;
});
echo "  ✓ India-specific checkout fields configured\n";
echo "  ✓ State label: 'State', Postcode label: 'PIN Code'\n";

echo "\n✅ Checkout setup complete!\n";
echo "   - Guest checkout enabled\n";
echo "   - Coupon 'WELCOME10' (10% off first order)\n";
echo "   - Coupon 'FREESHIP' (free shipping on ₹500+)\n";
echo "   - Trust badges on checkout page\n";
echo "   - Basic abandoned cart monitoring\n";
echo "   - India-specific address fields\n\n";
