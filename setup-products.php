<?php
/**
 * Product Structure Setup Script — TES-2
 * Run: php setup-products.php (from WordPress root)
 * Purpose: Create categories, attributes, and 5 sample ethnic wear products
 */

$_SERVER['HTTP_HOST']   = 'localhost:8081';
$_SERVER['REQUEST_URI'] = '/testcowork/';

require_once __DIR__ . '/wp-load.php';
wp_set_current_user(1);

if (!class_exists('WooCommerce')) {
    echo "Error: WooCommerce is not active. Run setup-woocommerce.php first.\n";
    exit(1);
}

echo "=== Product Setup Script — TES-2 ===\n\n";

// ─────────────────────────────────────────────────
// 3a. Product Categories
// ─────────────────────────────────────────────────
echo "[Step 1] Creating product categories...\n";

function create_product_category($name, $slug, $description = '', $parent = 0) {
    $existing = get_term_by('slug', $slug, 'product_cat');
    if ($existing) {
        echo "  → Category '$name' already exists (ID: {$existing->term_id}).\n";
        return $existing->term_id;
    }
    $result = wp_insert_term($name, 'product_cat', [
        'description' => $description,
        'slug'        => $slug,
        'parent'      => $parent,
    ]);
    if (is_wp_error($result)) {
        echo "  ✗ Failed to create '$name': " . $result->get_error_message() . "\n";
        return 0;
    }
    echo "  ✓ Created category: $name (ID: {$result['term_id']})\n";
    return $result['term_id'];
}

$cat_sarees     = create_product_category('Sarees', 'sarees', 'Handwoven and designer sarees for every occasion.');
$cat_salwar     = create_product_category('Salwar Suits', 'salwar-suits', 'Elegant salwar suits and Anarkali sets.');
$cat_lehengas   = create_product_category('Lehengas', 'lehengas', 'Bridal and festive lehenga sets.');
$cat_kurtis     = create_product_category('Kurtis', 'kurtis', 'Stylish ethnic kurtis for everyday wear.');
$cat_mens       = create_product_category("Men's Ethnic", 'mens-ethnic', "Traditional ethnic wear for men.");
$cat_sherwanis  = create_product_category('Sherwanis', 'sherwanis', 'Wedding and festive sherwanis.', $cat_mens);
$cat_kurta_pyj  = create_product_category('Kurta Pyjama', 'kurta-pyjama', 'Classic kurta pyjama sets.', $cat_mens);
$cat_dhoti      = create_product_category('Dhoti Sets', 'dhoti-sets', 'Traditional dhoti sets.', $cat_mens);

// ─────────────────────────────────────────────────
// 3b. Global Product Attributes
// ─────────────────────────────────────────────────
echo "\n[Step 2] Creating global product attributes...\n";

function create_product_attribute($name, $slug, $terms) {
    global $wpdb;

    // Check if attribute exists
    $attr_id = wc_attribute_taxonomy_id_by_name($slug);
    if (!$attr_id) {
        $attr_id = wc_create_attribute([
            'name'         => $name,
            'slug'         => $slug,
            'type'         => 'select',
            'order_by'     => 'menu_order',
            'has_archives' => false,
        ]);
        if (is_wp_error($attr_id)) {
            echo "  ✗ Failed to create attribute '$name': " . $attr_id->get_error_message() . "\n";
            return;
        }
        echo "  ✓ Attribute '$name' created (ID: $attr_id)\n";
    } else {
        echo "  → Attribute '$name' already exists.\n";
    }

    // Register taxonomy if not done yet
    $taxonomy = 'pa_' . $slug;
    if (!taxonomy_exists($taxonomy)) {
        register_taxonomy($taxonomy, 'product');
    }

    // Add terms
    foreach ($terms as $term) {
        if (!term_exists($term, $taxonomy)) {
            wp_insert_term($term, $taxonomy);
        }
    }
    echo "    → Terms added: " . implode(', ', $terms) . "\n";
}

create_product_attribute('Size', 'size', ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', 'Free Size']);
create_product_attribute('Color', 'color', ['Red', 'Blue', 'Green', 'Yellow', 'Pink', 'Purple', 'Orange', 'White', 'Black', 'Gold', 'Silver', 'Maroon', 'Navy Blue', 'Cream']);
create_product_attribute('Fabric', 'fabric', ['Silk', 'Cotton', 'Georgette', 'Chiffon', 'Banarasi', 'Chanderi', 'Linen', 'Rayon']);
create_product_attribute('Occasion', 'occasion', ['Wedding', 'Festive', 'Casual', 'Party', 'Office']);

// ─────────────────────────────────────────────────
// Helper: attach attribute to product
// ─────────────────────────────────────────────────
function attach_attribute($product, $attr_slug, $terms, $visible = true, $variation = false) {
    $taxonomy = 'pa_' . $attr_slug;
    if (!taxonomy_exists($taxonomy)) {
        register_taxonomy($taxonomy, 'product');
    }

    $term_ids = [];
    foreach ($terms as $term_name) {
        $t = get_term_by('name', $term_name, $taxonomy);
        if ($t) {
            $term_ids[] = $t->term_id;
        } else {
            $r = wp_insert_term($term_name, $taxonomy);
            if (!is_wp_error($r)) {
                $term_ids[] = $r['term_id'];
            }
        }
    }
    wp_set_object_terms($product->get_id(), $term_ids, $taxonomy, true);

    return [
        'name'         => $taxonomy,
        'value'        => '',
        'position'     => 0,
        'is_visible'   => $visible ? 1 : 0,
        'is_variation' => $variation ? 1 : 0,
        'is_taxonomy'  => 1,
    ];
}

// ─────────────────────────────────────────────────
// 3c. Sample Products
// ─────────────────────────────────────────────────
echo "\n[Step 3] Creating sample products...\n";

// ── Product 1: Banarasi Silk Saree ──────────────
echo "  Creating Product 1: Banarasi Silk Saree...\n";
$p1 = get_page_by_title('Banarasi Silk Saree', OBJECT, 'product');
if (!$p1) {
    $product1 = new WC_Product_Simple();
    $product1->set_name('Banarasi Silk Saree');
    $product1->set_status('publish');
    $product1->set_description('Experience the royal elegance of a handwoven pure Banarasi silk saree. Each saree is crafted by skilled artisans using traditional weaving techniques passed down through generations. Perfect for weddings and festive occasions.');
    $product1->set_short_description('Handwoven pure Banarasi silk saree with intricate gold zari work.');
    $product1->set_regular_price('4999');
    $product1->set_sale_price('3999');
    $product1->set_manage_stock(true);
    $product1->set_stock_quantity(50);
    $product1->set_stock_status('instock');
    $product1->set_featured(true);
    $product1->set_category_ids([$cat_sarees]);
    $product1->set_sku('BNRS-001');

    // Set placeholder image
    $product1->set_image_id(0);

    $product1->save();

    // Set attributes
    $attrs = [
        attach_attribute($product1, 'color', ['Red', 'Gold', 'Maroon']),
        attach_attribute($product1, 'fabric', ['Silk', 'Banarasi']),
        attach_attribute($product1, 'occasion', ['Wedding', 'Festive']),
    ];
    update_post_meta($product1->get_id(), '_product_attributes', $attrs);

    echo "  ✓ Product 1 created (ID: " . $product1->get_id() . ")\n";
} else {
    echo "  → Product 1 already exists.\n";
}

// ── Product 2: Anarkali Salwar Suit (Variable) ──
echo "  Creating Product 2: Anarkali Salwar Suit...\n";
$p2 = get_page_by_title('Anarkali Salwar Suit', OBJECT, 'product');
if (!$p2) {
    $product2 = new WC_Product_Variable();
    $product2->set_name('Anarkali Salwar Suit');
    $product2->set_status('publish');
    $product2->set_description('Stunning Anarkali salwar suit with intricate embroidery and flowing silhouette. Available in multiple sizes and colors to suit every body type and preference.');
    $product2->set_short_description('Embroidered Anarkali salwar suit with dupatta.');
    $product2->set_regular_price('2499');
    $product2->set_sale_price('1999');
    $product2->set_featured(true);
    $product2->set_category_ids([$cat_salwar]);
    $product2->set_sku('ANK-002');

    // Save to get ID
    $product2->save();

    // Attach attributes for variations
    $size_terms  = ['S', 'M', 'L', 'XL'];
    $color_terms = ['Blue', 'Pink', 'Green'];

    $size_tax  = 'pa_size';
    $color_tax = 'pa_color';

    // Assign terms
    $size_ids  = [];
    $color_ids = [];

    foreach ($size_terms as $t) {
        $term = get_term_by('name', $t, $size_tax);
        if ($term) $size_ids[] = $term->term_id;
    }
    foreach ($color_terms as $t) {
        $term = get_term_by('name', $t, $color_tax);
        if ($term) $color_ids[] = $term->term_id;
    }

    wp_set_object_terms($product2->get_id(), $size_ids, $size_tax, false);
    wp_set_object_terms($product2->get_id(), $color_ids, $color_tax, false);

    update_post_meta($product2->get_id(), '_product_attributes', [
        $size_tax => [
            'name' => $size_tax, 'value' => '', 'position' => 0,
            'is_visible' => 1, 'is_variation' => 1, 'is_taxonomy' => 1,
        ],
        $color_tax => [
            'name' => $color_tax, 'value' => '', 'position' => 1,
            'is_visible' => 1, 'is_variation' => 1, 'is_taxonomy' => 1,
        ],
    ]);

    // Create variations
    foreach ($size_terms as $size) {
        foreach ($color_terms as $color) {
            $variation = new WC_Product_Variation();
            $variation->set_parent_id($product2->get_id());
            $variation->set_attributes([
                'pa_size'  => strtolower($size),
                'pa_color' => strtolower($color),
            ]);
            $variation->set_regular_price('2499');
            $variation->set_sale_price('1999');
            $variation->set_manage_stock(true);
            $variation->set_stock_quantity(30);
            $variation->set_status('publish');
            $variation->save();
        }
    }

    echo "  ✓ Product 2 created with " . (count($size_terms) * count($color_terms)) . " variations (ID: " . $product2->get_id() . ")\n";
} else {
    echo "  → Product 2 already exists.\n";
}

// ── Product 3: Bridal Lehenga Set (Variable) ────
echo "  Creating Product 3: Bridal Lehenga Set...\n";
$p3 = get_page_by_title('Bridal Lehenga Set', OBJECT, 'product');
if (!$p3) {
    $product3 = new WC_Product_Variable();
    $product3->set_name('Bridal Lehenga Set');
    $product3->set_status('publish');
    $product3->set_description('Exquisite bridal lehenga set with heavy embroidery, sequin work, and matching blouse and dupatta. Perfect for Indian weddings and grand ceremonies. Custom sizing available.');
    $product3->set_short_description('Heavy embroidered bridal lehenga with blouse and dupatta.');
    $product3->set_regular_price('15999');
    $product3->set_featured(true);
    $product3->set_category_ids([$cat_lehengas]);
    $product3->set_sku('LHN-003');
    $product3->save();

    $size_terms  = ['S', 'M', 'L', 'XL', 'Custom'];
    $color_terms = ['Red', 'Pink', 'Maroon'];

    foreach ($size_terms as $size) {
        $term = get_term_by('name', $size, 'pa_size');
        if (!$term) wp_insert_term($size, 'pa_size');
    }

    $size_ids  = [];
    $color_ids = [];
    foreach ($size_terms as $t) {
        $term = get_term_by('name', $t, 'pa_size');
        if ($term) $size_ids[] = $term->term_id;
    }
    foreach ($color_terms as $t) {
        $term = get_term_by('name', $t, 'pa_color');
        if ($term) $color_ids[] = $term->term_id;
    }

    wp_set_object_terms($product3->get_id(), $size_ids, 'pa_size', false);
    wp_set_object_terms($product3->get_id(), $color_ids, 'pa_color', false);
    update_post_meta($product3->get_id(), '_product_attributes', [
        'pa_size'  => ['name' => 'pa_size', 'value' => '', 'position' => 0, 'is_visible' => 1, 'is_variation' => 1, 'is_taxonomy' => 1],
        'pa_color' => ['name' => 'pa_color', 'value' => '', 'position' => 1, 'is_visible' => 1, 'is_variation' => 1, 'is_taxonomy' => 1],
    ]);

    foreach ($size_terms as $size) {
        foreach ($color_terms as $color) {
            $v = new WC_Product_Variation();
            $v->set_parent_id($product3->get_id());
            $v->set_attributes(['pa_size' => strtolower($size), 'pa_color' => strtolower($color)]);
            $v->set_regular_price('15999');
            $v->set_manage_stock(true);
            $v->set_stock_quantity(10);
            $v->set_status('publish');
            $v->save();
        }
    }

    echo "  ✓ Product 3 created (ID: " . $product3->get_id() . ")\n";
} else {
    echo "  → Product 3 already exists.\n";
}

// ── Product 4: Men's Silk Kurta Pyjama (Variable) ──
echo "  Creating Product 4: Men's Silk Kurta Pyjama...\n";
$p4 = get_page_by_title("Men's Silk Kurta Pyjama", OBJECT, 'product');
if (!$p4) {
    $product4 = new WC_Product_Variable();
    $product4->set_name("Men's Silk Kurta Pyjama");
    $product4->set_status('publish');
    $product4->set_description("Premium silk kurta pyjama set for men. Crafted from fine silk fabric with subtle embroidery on the collar and cuffs. Perfect for festive occasions, pujas, and family celebrations.");
    $product4->set_short_description("Premium silk kurta pyjama set for men with embroidery details.");
    $product4->set_regular_price('1999');
    $product4->set_sale_price('1499');
    $product4->set_featured(false);
    $product4->set_category_ids([$cat_kurta_pyj]);
    $product4->set_sku('KRT-004');
    $product4->save();

    $size_terms  = ['S', 'M', 'L', 'XL', 'XXL'];
    $color_terms = ['White', 'Cream', 'Blue', 'Yellow'];

    $size_ids = $color_ids = [];
    foreach ($size_terms as $t) {
        $term = get_term_by('name', $t, 'pa_size');
        if ($term) $size_ids[] = $term->term_id;
    }
    foreach ($color_terms as $t) {
        $term = get_term_by('name', $t, 'pa_color');
        if ($term) $color_ids[] = $term->term_id;
    }
    wp_set_object_terms($product4->get_id(), $size_ids, 'pa_size', false);
    wp_set_object_terms($product4->get_id(), $color_ids, 'pa_color', false);
    update_post_meta($product4->get_id(), '_product_attributes', [
        'pa_size'  => ['name' => 'pa_size', 'value' => '', 'position' => 0, 'is_visible' => 1, 'is_variation' => 1, 'is_taxonomy' => 1],
        'pa_color' => ['name' => 'pa_color', 'value' => '', 'position' => 1, 'is_visible' => 1, 'is_variation' => 1, 'is_taxonomy' => 1],
    ]);

    foreach ($size_terms as $size) {
        foreach ($color_terms as $color) {
            $v = new WC_Product_Variation();
            $v->set_parent_id($product4->get_id());
            $v->set_attributes(['pa_size' => strtolower($size), 'pa_color' => strtolower($color)]);
            $v->set_regular_price('1999');
            $v->set_sale_price('1499');
            $v->set_manage_stock(true);
            $v->set_stock_quantity(40);
            $v->set_status('publish');
            $v->save();
        }
    }
    echo "  ✓ Product 4 created (ID: " . $product4->get_id() . ")\n";
} else {
    echo "  → Product 4 already exists.\n";
}

// ── Product 5: Georgette Kurti (Variable) ───────
echo "  Creating Product 5: Georgette Kurti...\n";
$p5 = get_page_by_title('Georgette Kurti', OBJECT, 'product');
if (!$p5) {
    $product5 = new WC_Product_Variable();
    $product5->set_name('Georgette Kurti');
    $product5->set_status('publish');
    $product5->set_description('Lightweight and flowy georgette kurti with beautiful block prints. Ideal for casual outings, office wear, and everyday ethnic looks. Available in a range of vibrant colors.');
    $product5->set_short_description('Flowy georgette kurti with block print design.');
    $product5->set_regular_price('899');
    $product5->set_featured(false);
    $product5->set_category_ids([$cat_kurtis]);
    $product5->set_sku('GKT-005');
    $product5->save();

    $size_terms  = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
    $color_terms = ['Pink', 'Blue', 'Green', 'Orange'];

    $size_ids = $color_ids = [];
    foreach ($size_terms as $t) {
        $term = get_term_by('name', $t, 'pa_size');
        if ($term) $size_ids[] = $term->term_id;
    }
    foreach ($color_terms as $t) {
        $term = get_term_by('name', $t, 'pa_color');
        if ($term) $color_ids[] = $term->term_id;
    }
    wp_set_object_terms($product5->get_id(), $size_ids, 'pa_size', false);
    wp_set_object_terms($product5->get_id(), $color_ids, 'pa_color', false);
    update_post_meta($product5->get_id(), '_product_attributes', [
        'pa_size'  => ['name' => 'pa_size', 'value' => '', 'position' => 0, 'is_visible' => 1, 'is_variation' => 1, 'is_taxonomy' => 1],
        'pa_color' => ['name' => 'pa_color', 'value' => '', 'position' => 1, 'is_visible' => 1, 'is_variation' => 1, 'is_taxonomy' => 1],
    ]);

    foreach ($size_terms as $size) {
        foreach ($color_terms as $color) {
            $v = new WC_Product_Variation();
            $v->set_parent_id($product5->get_id());
            $v->set_attributes(['pa_size' => strtolower($size), 'pa_color' => strtolower($color)]);
            $v->set_regular_price('899');
            $v->set_manage_stock(true);
            $v->set_stock_quantity(60);
            $v->set_status('publish');
            $v->save();
        }
    }
    echo "  ✓ Product 5 created (ID: " . $product5->get_id() . ")\n";
} else {
    echo "  → Product 5 already exists.\n";
}

// Sync product lookup table
if (function_exists('wc_update_product_lookup_tables')) {
    wc_update_product_lookup_tables();
}

echo "\n✅ Product setup complete!\n";
echo "   Categories: 8 created (3 parent + 5 top-level)\n";
echo "   Attributes: Size, Color, Fabric, Occasion\n";
echo "   Products: 5 created (1 simple + 4 variable)\n\n";
