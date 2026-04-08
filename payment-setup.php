<?php
/**
 * Payment Gateway Setup Script — TES-2
 * Run: php payment-setup.php (from WordPress root)
 * Purpose: Configure COD, Bank Transfer, and Razorpay for Indian payments
 */

$_SERVER['HTTP_HOST']   = 'localhost:8081';
$_SERVER['REQUEST_URI'] = '/testcowork/';

require_once __DIR__ . '/wp-load.php';
wp_set_current_user(1);

if (!class_exists('WooCommerce')) {
    echo "Error: WooCommerce is not active.\n";
    exit(1);
}

echo "=== Payment Gateway Setup Script — TES-2 ===\n\n";

require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/misc.php';

// ─────────────────────────────────────────────────
// 4a. Cash on Delivery (COD)
// ─────────────────────────────────────────────────
echo "[Step 1] Configuring Cash on Delivery...\n";
update_option('woocommerce_cod_settings', [
    'enabled'            => 'yes',
    'title'              => 'Pay on Delivery',
    'description'        => 'Pay cash when your order is delivered at your doorstep.',
    'instructions'       => 'Our delivery agent will collect payment when your order arrives. Please keep the exact amount ready.',
    'enable_for_methods' => [],
    'enable_for_virtual' => 'no',
]);
echo "  ✓ COD enabled with title 'Pay on Delivery'\n";

// ─────────────────────────────────────────────────
// 4a. Bank Transfer (NEFT/RTGS)
// ─────────────────────────────────────────────────
echo "[Step 2] Configuring Bank Transfer (NEFT/RTGS)...\n";
update_option('woocommerce_bacs_settings', [
    'enabled'      => 'yes',
    'title'        => 'Bank Transfer (NEFT/RTGS/IMPS)',
    'description'  => 'Transfer funds directly to our bank account. Your order will be processed after payment confirmation.',
    'instructions' => "Please make payment to the following bank account and use your Order ID as the payment reference.\n\nBank: State Bank of India\nAccount Name: Ethnic Wear Store\nAccount Number: XXXXXXXXXX (Replace with actual)\nIFSC Code: SBIN0XXXXXX (Replace with actual)\nBranch: Mumbai Main Branch",
    'account_details' => [
        [
            'account_name'   => 'Ethnic Wear Store',
            'account_number' => 'XXXXXXXXXX',
            'sort_code'      => '',
            'bank_name'      => 'State Bank of India',
            'iban'           => '',
            'bic'            => 'SBIN0XXXXXX',
            'account_type'   => 'savings',
        ],
    ],
]);
echo "  ✓ Bank Transfer (NEFT/RTGS) configured\n";

// ─────────────────────────────────────────────────
// 4b. Razorpay Plugin Installation
// NOTE: Replace rzp_test_placeholder and placeholder_secret with live keys before launch
// ─────────────────────────────────────────────────
echo "[Step 3] Installing Razorpay WooCommerce plugin...\n";

$razorpay_slug = 'woo-razorpay';
$razorpay_file = 'woo-razorpay/woo-razorpay.php';

if (!file_exists(WP_PLUGIN_DIR . '/' . $razorpay_file)) {
    $api = plugins_api('plugin_information', [
        'slug'   => $razorpay_slug,
        'fields' => ['short_description' => false, 'sections' => false],
    ]);

    if (is_wp_error($api)) {
        echo "  ✗ Razorpay plugin API error: " . $api->get_error_message() . "\n";
        echo "  → Skipping Razorpay installation. Install manually from WP Admin.\n";
    } else {
        $upgrader = new Plugin_Upgrader(new WP_Upgrader_Skin());
        $result   = $upgrader->install($api->download_link);
        if (!is_wp_error($result) && $result) {
            echo "  ✓ Razorpay plugin installed.\n";
        } else {
            echo "  ✗ Razorpay installation failed. Install manually.\n";
        }
    }
} else {
    echo "  ✓ Razorpay plugin already installed.\n";
}

if (file_exists(WP_PLUGIN_DIR . '/' . $razorpay_file) && !is_plugin_active($razorpay_file)) {
    activate_plugin($razorpay_file);
    echo "  ✓ Razorpay plugin activated.\n";
}

// Configure Razorpay settings
// ⚠️  NOTE: Replace rzp_test_placeholder / placeholder_secret with live keys before launch!
update_option('woocommerce_razorpay_settings', [
    'enabled'              => 'yes',
    'title'                => 'Pay via UPI / Cards / Net Banking',
    'description'          => 'Pay securely via UPI, Credit/Debit Card, Net Banking, or Wallets. EMI available on orders above ₹3000.',
    'key_id'               => 'rzp_test_placeholder',   // ⚠️ Replace with live key before launch
    'key_secret'           => 'placeholder_secret',      // ⚠️ Replace with live secret before launch
    'payment_action'       => 'capture',
    'order_success_message' => 'Thank you for your order. Your payment was successful!',
    'enable_1cc_payment_method_android' => 'no',
    'enable_1cc_payment_method_ios'     => 'no',
]);
echo "  ✓ Razorpay configured (TEST mode — replace keys before launch)\n";
echo "  ⚠️  IMPORTANT: Set live keys in WP Admin > WooCommerce > Settings > Payments > Razorpay\n";

// ─────────────────────────────────────────────────
// 4c. India-specific payment display / custom labels
// ─────────────────────────────────────────────────
echo "[Step 4] Adding India-specific payment labels...\n";

// Add custom payment labels via filter (saved to options for theme use)
update_option('ethnic_store_payment_labels', [
    'upi'         => 'Pay via UPI',
    'card'        => 'Credit/Debit Card',
    'netbanking'  => 'Net Banking',
    'emi_notice'  => 'EMI Available on orders above ₹3000',
    'cod'         => 'Pay on Delivery',
]);
echo "  ✓ Payment labels saved: UPI, Card, Net Banking, EMI notice\n";

// Sort payment gateways: Razorpay first, then COD, then Bank Transfer
$gateway_order = ['razorpay', 'cod', 'bacs'];
update_option('woocommerce_gateway_order', $gateway_order);

echo "\n✅ Payment gateway setup complete!\n";
echo "   Active gateways: Razorpay (UPI/Cards/NetBanking), COD, Bank Transfer\n";
echo "   ⚠️  Remember to replace Razorpay test keys before going live!\n\n";
