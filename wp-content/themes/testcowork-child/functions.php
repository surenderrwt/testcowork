<?php
/**
 * Testcowork Child Theme — functions.php
 * TES-2: Ethnic Wear Store
 */

if (!defined('ABSPATH')) exit;

// ── Enqueue parent + child stylesheets ───────────────────
add_action('wp_enqueue_scripts', function () {
    // Google Fonts — Playfair Display + Lato
    wp_enqueue_style(
        'ethnic-google-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Lato:wght@400;600;700&display=swap',
        [],
        null
    );

    // Parent theme
    wp_enqueue_style(
        'parent-style',
        get_template_directory_uri() . '/style.css'
    );

    // Child theme
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        ['parent-style'],
        wp_get_theme()->get('Version')
    );

    // Custom store JS
    wp_enqueue_script(
        'ethnic-store-js',
        get_stylesheet_directory_uri() . '/assets/js/ethnic-store.js',
        ['jquery'],
        '1.0.0',
        true
    );

    // Pass PHP vars to JS
    wp_localize_script('ethnic-store-js', 'ethnicStore', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('ethnic_store_nonce'),
    ]);
}, 20);

// ── WooCommerce support ───────────────────────────────────
add_action('after_setup_theme', function () {
    add_theme_support('woocommerce', [
        'thumbnail_image_width'         => 600,
        'gallery_thumbnail_image_width' => 100,
        'single_image_width'            => 800,
    ]);
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'script', 'style']);
});

// ── Trust badges on checkout ──────────────────────────────
add_action('woocommerce_review_order_before_submit', function () {
    echo '<div class="trust-badges">';
    echo '  <div class="trust-badge">🔒 Secure Payment</div>';
    echo '  <div class="trust-badge">↩️ Easy Returns</div>';
    echo '  <div class="trust-badge">✅ Genuine Products</div>';
    echo '  <div class="trust-badge">🚚 Fast Delivery</div>';
    echo '</div>';
});

// ── Trust badges on single product page ──────────────────
add_action('woocommerce_single_product_summary', function () {
    echo '<div class="trust-badges">';
    echo '  <div class="trust-badge">🔒 Secure Payment</div>';
    echo '  <div class="trust-badge">↩️ Easy Returns</div>';
    echo '  <div class="trust-badge">✅ Genuine Products</div>';
    echo '</div>';
}, 35);

// ── Size guide button on product pages ───────────────────
add_action('woocommerce_single_product_summary', function () {
    global $product;
    if (!$product) return;
    // Only show for clothing categories
    $clothing_cats = ['sarees', 'salwar-suits', 'lehengas', 'kurtis', 'mens-ethnic', 'kurta-pyjama', 'sherwanis'];
    $terms = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'slugs']);
    if (array_intersect($clothing_cats, $terms)) {
        echo '<a href="#size-guide" class="size-guide-btn" id="open-size-guide">View Size Guide</a>';
        echo '<div id="size-guide" style="display:none;background:var(--color-accent);padding:16px;border-radius:8px;margin-top:12px;border:1px solid var(--color-border);">';
        echo '  <h4 style="color:var(--color-primary);margin-top:0">Size Guide (in inches)</h4>';
        echo '  <table style="width:100%;border-collapse:collapse;font-size:.9rem;">';
        echo '    <tr style="background:var(--color-primary);color:#fff;"><th style="padding:8px">Size</th><th style="padding:8px">Chest</th><th style="padding:8px">Waist</th><th style="padding:8px">Hip</th></tr>';
        echo '    <tr><td style="padding:8px;text-align:center">XS</td><td style="padding:8px;text-align:center">32</td><td style="padding:8px;text-align:center">26</td><td style="padding:8px;text-align:center">36</td></tr>';
        echo '    <tr style="background:#f9f5e0"><td style="padding:8px;text-align:center">S</td><td style="padding:8px;text-align:center">34</td><td style="padding:8px;text-align:center">28</td><td style="padding:8px;text-align:center">38</td></tr>';
        echo '    <tr><td style="padding:8px;text-align:center">M</td><td style="padding:8px;text-align:center">36</td><td style="padding:8px;text-align:center">30</td><td style="padding:8px;text-align:center">40</td></tr>';
        echo '    <tr style="background:#f9f5e0"><td style="padding:8px;text-align:center">L</td><td style="padding:8px;text-align:center">38</td><td style="padding:8px;text-align:center">32</td><td style="padding:8px;text-align:center">42</td></tr>';
        echo '    <tr><td style="padding:8px;text-align:center">XL</td><td style="padding:8px;text-align:center">40</td><td style="padding:8px;text-align:center">34</td><td style="padding:8px;text-align:center">44</td></tr>';
        echo '    <tr style="background:#f9f5e0"><td style="padding:8px;text-align:center">XXL</td><td style="padding:8px;text-align:center">42</td><td style="padding:8px;text-align:center">36</td><td style="padding:8px;text-align:center">46</td></tr>';
        echo '  </table>';
        echo '</div>';
    }
}, 25);

// ── Sticky add-to-cart on mobile ──────────────────────────
add_action('woocommerce_after_single_product', function () {
    global $product;
    if (!$product || !$product->is_purchasable()) return;
    ?>
    <div class="sticky-add-to-cart" id="sticky-add-to-cart" style="display:none">
        <div class="product-title"><?php echo esc_html($product->get_name()); ?></div>
        <div class="sticky-price"><?php echo wp_kses_post($product->get_price_html()); ?></div>
        <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="button alt">Add to Cart</a>
    </div>
    <?php
});

// ── EMI notice on cart/checkout ───────────────────────────
add_action('woocommerce_before_checkout_form', function () {
    $total = WC()->cart ? WC()->cart->get_cart_contents_total() : 0;
    if ($total >= 3000) {
        echo '<div class="woocommerce-info" style="border-left-color:var(--color-secondary)">💳 EMI Available on this order via Razorpay. Select EMI option in payment step.</div>';
    }
}, 5);

// ── Free shipping progress bar ────────────────────────────
add_action('woocommerce_before_cart', function () {
    $total     = WC()->cart ? WC()->cart->get_cart_contents_total() : 0;
    $threshold = 999;
    $remaining = max(0, $threshold - $total);
    if ($remaining > 0) {
        $pct = min(100, round(($total / $threshold) * 100));
        echo '<div style="background:var(--color-accent);border:1px solid var(--color-border);padding:16px;border-radius:8px;margin-bottom:20px;">';
        echo '  <p style="margin:0 0 8px;color:var(--color-primary);font-weight:600;">🚚 Add <strong>₹' . number_format($remaining, 2) . '</strong> more for FREE shipping!</p>';
        echo '  <div style="background:#ddd;border-radius:50px;height:8px">';
        echo '    <div style="background:var(--color-primary);width:' . $pct . '%;height:8px;border-radius:50px;transition:.3s"></div>';
        echo '  </div>';
        echo '</div>';
    } else {
        echo '<div class="woocommerce-info" style="border-left-color:#4CAF50">🎉 You qualify for FREE shipping!</div>';
    }
});

// ── Homepage template shortcodes ─────────────────────────

// [ethnic_hero] shortcode
add_shortcode('ethnic_hero', function () {
    ob_start();
    ?>
    <div class="ethnic-hero">
        <h1>Explore India's Finest Ethnic Wear</h1>
        <p>Handcrafted sarees, salwar suits, lehengas & more — celebrate every occasion in style.</p>
        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="hero-cta">Shop Now</a>
    </div>
    <?php
    return ob_get_clean();
});

// [ethnic_categories] shortcode
add_shortcode('ethnic_categories', function () {
    $cats = [
        ['slug' => 'sarees',      'name' => 'Sarees',       'icon' => '🥻'],
        ['slug' => 'salwar-suits','name' => 'Salwar Suits',  'icon' => '👗'],
        ['slug' => 'lehengas',    'name' => 'Lehengas',      'icon' => '💃'],
        ['slug' => 'kurtis',      'name' => 'Kurtis',        'icon' => '👘'],
        ['slug' => 'mens-ethnic', 'name' => "Men's Ethnic",  'icon' => '🧦'],
    ];
    ob_start();
    echo '<section style="max-width:1200px;margin:0 auto;padding:40px 20px">';
    echo '  <h2 style="text-align:center;color:var(--color-primary);font-family:var(--font-heading);margin-bottom:30px">Shop by Category</h2>';
    echo '  <div class="category-grid">';
    foreach ($cats as $cat) {
        $term = get_term_by('slug', $cat['slug'], 'product_cat');
        $url  = $term ? get_term_link($term) : '#';
        echo '<a href="' . esc_url($url) . '" class="category-card">';
        echo '  <div class="cat-icon">' . $cat['icon'] . '</div>';
        echo '  <h3>' . esc_html($cat['name']) . '</h3>';
        echo '</a>';
    }
    echo '  </div>';
    echo '</section>';
    return ob_get_clean();
});

// [offer_banner] shortcode
add_shortcode('offer_banner', function () {
    return '<div class="offer-banner">🚚 FREE Shipping on orders above ₹999 &nbsp;|&nbsp; 🎁 Use code <strong>WELCOME10</strong> for 10% off your first order!</div>';
});

// [new_arrivals] shortcode — shows 6 newest products
add_shortcode('new_arrivals', function ($atts) {
    $a = shortcode_atts(['count' => 6, 'heading' => 'New Arrivals'], $atts);
    $q = new WC_Product_Query([
        'limit'   => (int) $a['count'],
        'orderby' => 'date',
        'order'   => 'DESC',
        'status'  => 'publish',
    ]);
    $products = $q->get_products();
    if (empty($products)) return '';

    ob_start();
    echo '<section style="max-width:1200px;margin:0 auto;padding:20px 20px 40px">';
    echo '  <h2 style="text-align:center;color:var(--color-primary);font-family:var(--font-heading);margin-bottom:30px">' . esc_html($a['heading']) . '</h2>';
    echo '  <ul class="products">';
    foreach ($products as $product) {
        wc_get_template_part('content', 'product');
    }
    echo '  </ul>';
    echo '  <div style="text-align:center;margin-top:30px"><a href="' . esc_url(wc_get_page_permalink('shop')) . '" class="button">View All Products →</a></div>';
    echo '</section>';
    return ob_get_clean();
});

// ── Add GST field to checkout ─────────────────────────────
add_filter('woocommerce_checkout_fields', function ($fields) {
    $fields['billing']['billing_gst_number'] = [
        'type'     => 'text',
        'label'    => 'GST Number (Optional — for business buyers)',
        'required' => false,
        'class'    => ['form-row-wide'],
        'priority' => 120,
    ];
    return $fields;
});

// Save GST number to order meta
add_action('woocommerce_checkout_update_order_meta', function ($order_id) {
    if (!empty($_POST['billing_gst_number'])) {
        update_post_meta($order_id, '_billing_gst_number', sanitize_text_field($_POST['billing_gst_number']));
    }
});

// Display GST in order admin
add_action('woocommerce_admin_order_data_after_billing_address', function ($order) {
    $gst = get_post_meta($order->get_id(), '_billing_gst_number', true);
    if ($gst) {
        echo '<p><strong>GST Number:</strong> ' . esc_html($gst) . '</p>';
    }
});

// ── Remove WooCommerce default styles (use ours) ──────────
add_filter('woocommerce_enqueue_styles', function ($styles) {
    // Keep WC base styles but remove opinionated layout styles
    unset($styles['woocommerce-layout']);
    unset($styles['woocommerce-smallscreen']);
    return $styles;
});
