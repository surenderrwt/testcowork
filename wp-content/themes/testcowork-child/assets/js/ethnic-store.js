/**
 * Ethnic Store — Custom JavaScript
 * TES-2: WooCommerce Ethnic Wear Store
 */
(function ($) {
    'use strict';

    // ── Sticky Add-to-Cart (mobile) ─────────────────────────
    function initStickyCart() {
        var $sticky    = $('#sticky-add-to-cart');
        var $addToCart = $('.single_add_to_cart_button');
        if (!$sticky.length || !$addToCart.length) return;

        $(window).on('scroll', function () {
            var offset = $addToCart.offset().top + $addToCart.outerHeight();
            if ($(window).scrollTop() > offset && $(window).width() <= 768) {
                $sticky.fadeIn(200);
            } else {
                $sticky.fadeOut(200);
            }
        });
    }

    // ── Size Guide Toggle ────────────────────────────────────
    function initSizeGuide() {
        $('#open-size-guide').on('click', function (e) {
            e.preventDefault();
            var $guide = $('#size-guide');
            if ($guide.is(':visible')) {
                $guide.slideUp(200);
                $(this).text('View Size Guide');
            } else {
                $guide.slideDown(200);
                $(this).text('Close Size Guide');
            }
        });
    }

    // ── Variation Select → Visual Swatches ───────────────────
    function initSwatches() {
        // Map CSS color names for swatches
        var colorMap = {
            'red': '#C0392B', 'blue': '#2980B9', 'green': '#27AE60',
            'yellow': '#F1C40F', 'pink': '#FF69B4', 'purple': '#8E44AD',
            'orange': '#E67E22', 'white': '#FFFFFF', 'black': '#1a1a1a',
            'gold': '#DAA520', 'silver': '#C0C0C0', 'maroon': '#800000',
            'navy blue': '#001F5B', 'navy_blue': '#001F5B', 'cream': '#FFFDD0',
        };

        $('.variations tr').each(function () {
            var $label = $(this).find('td.label label');
            var $select = $(this).find('td.value select');
            if (!$select.length) return;

            var attrName = ($select.attr('id') || '').toLowerCase();
            if (attrName.indexOf('color') === -1 && attrName.indexOf('colour') === -1) return;

            // Build swatch container
            var $container = $('<div class="ethnic-swatch-container"></div>');
            $select.find('option').each(function () {
                var val = $(this).val();
                if (!val) return;
                var label = $(this).text().trim();
                var colorKey = label.toLowerCase().replace(/ /g, '_');
                var bg = colorMap[colorKey] || colorMap[label.toLowerCase()] || '#aaa';
                var $swatch = $('<span>')
                    .addClass('ethnic-color-swatch')
                    .attr({'title': label, 'data-value': val})
                    .css({
                        'background-color': bg,
                        'box-shadow': bg === '#FFFFFF' ? '0 0 0 1px #ccc inset' : 'none',
                    });
                $container.append($swatch);
            });

            $container.on('click', '.ethnic-color-swatch', function () {
                var val = $(this).data('value');
                $container.find('.ethnic-color-swatch').removeClass('selected');
                $(this).addClass('selected');
                $select.val(val).trigger('change');
            });

            // Sync swatches when variation changes programmatically
            $select.on('change', function () {
                var val = $(this).val();
                $container.find('.ethnic-color-swatch').removeClass('selected');
                $container.find('[data-value="' + val + '"]').addClass('selected');
            });

            $select.hide().after($container);
        });
    }

    // ── Size buttons ─────────────────────────────────────────
    function initSizeButtons() {
        $('.variations tr').each(function () {
            var $select = $(this).find('td.value select');
            if (!$select.length) return;
            var attrName = ($select.attr('id') || '').toLowerCase();
            if (attrName.indexOf('size') === -1) return;

            var $container = $('<div class="ethnic-size-buttons"></div>');
            $select.find('option').each(function () {
                var val = $(this).val();
                if (!val) return;
                var label = $(this).text().trim();
                var $btn = $('<button type="button">')
                    .addClass('ethnic-size-btn')
                    .attr('data-value', val)
                    .text(label);
                $container.append($btn);
            });

            $container.on('click', '.ethnic-size-btn', function () {
                var val = $(this).data('value');
                $container.find('.ethnic-size-btn').removeClass('selected');
                $(this).addClass('selected');
                $select.val(val).trigger('change');
            });

            $select.on('change', function () {
                var val = $(this).val();
                $container.find('.ethnic-size-btn').removeClass('selected');
                $container.find('[data-value="' + val + '"]').addClass('selected');
            });

            $select.hide().after($container);
        });
    }

    // ── Quantity +/- buttons (enhancement) ───────────────────
    function initQtyButtons() {
        if ($('.quantity').find('.qty-btn').length) return;
        $('.quantity').each(function () {
            var $qty = $(this).find('input.qty');
            if (!$qty.length) return;
            var $minus = $('<button type="button" class="qty-btn qty-minus" aria-label="Decrease quantity">−</button>');
            var $plus  = $('<button type="button" class="qty-btn qty-plus"  aria-label="Increase quantity">+</button>');
            $qty.before($minus).after($plus);
        });

        $(document).on('click', '.qty-minus', function () {
            var $qty = $(this).siblings('input.qty');
            var val  = parseInt($qty.val(), 10) || 1;
            var min  = parseInt($qty.attr('min'), 10) || 1;
            if (val > min) { $qty.val(val - 1).trigger('change'); }
        });
        $(document).on('click', '.qty-plus', function () {
            var $qty = $(this).siblings('input.qty');
            var val  = parseInt($qty.val(), 10) || 1;
            var max  = parseInt($qty.attr('max'), 10) || 9999;
            if (val < max) { $qty.val(val + 1).trigger('change'); }
        });
    }

    // ── Init on DOM ready ────────────────────────────────────
    $(function () {
        initStickyCart();
        initSizeGuide();
        initSwatches();
        initSizeButtons();
        initQtyButtons();

        // Re-init swatches & size buttons after variation update
        $(document.body).on('wc_variation_form', function () {
            setTimeout(function () {
                initSwatches();
                initSizeButtons();
            }, 300);
        });
    });

}(jQuery));
