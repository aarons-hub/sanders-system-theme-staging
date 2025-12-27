<?php
// Ensure post thumbnails are enabled for the theme
add_theme_support( 'post-thumbnails' );

// Add editor styles support
add_theme_support( 'editor-styles' );
add_editor_style( 'css/editor-style.css' );

function theme_fonts() {
    // Enqueue theme fonts
    wp_enqueue_style(
        'sanders-system-theme-font-inter',
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&display=swap',
        array('sanders-system-theme-fonts'),
        null
    );
    wp_enqueue_style(
        'sanders-system-theme-font-raleway',
        'https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap',
        array('sanders-system-theme-fonts'),
        null
    );
}


function theme_style() {
    // Enqueue the parent theme's stylesheet
    wp_enqueue_style(
        'twentytwentyfive-style',
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme()->get('Version')
    );
    // Enqueue Swiper stylesheet
    wp_enqueue_style(
        'swiper-style',
        'https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css',
        array(),
        wp_get_theme()->get('Version')
    );
    // Enqueue the child theme's stylesheet
    wp_enqueue_style(
        'sanders-system-2025-child-theme-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('twentytwentyfive-style'),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'theme_fonts');
add_action('wp_enqueue_scripts', 'theme_style');


// Enqueue GSAP scripts
function gsap_enqueue_scripts() {
    // Enqueue GSAP core library from CDN
    wp_enqueue_script(
        'gsap',
        'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js',
        array(),
        '3.12.5',
        true
    );
    // Enqueue GSAP ScrollTrigger plugin
    wp_enqueue_script(
        'gsap-scrolltrigger',
        'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js',
        array('gsap'),
        '3.12.5',
        true
    );
    // Enqueue GSAP ScrollToPlugin
    wp_enqueue_script(
        'gsap-scrollto',
        'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollToPlugin.min.js',
        array('gsap'),
        '3.12.5',
        true
    );
    // Enqueue GSAP ScrollSmoother plugin
    wp_enqueue_script(
        'gsap-scrollsmoother',
        'https://assets.codepen.io/16327/ScrollSmoother.min.js',
        array('gsap', 'gsap-scrolltrigger'),
        '3.12.5',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'gsap_enqueue_scripts' );


function swiper_enqueue_scripts() {
    // Enqueue Swiper script from CDN
    wp_enqueue_script(
        'swiper-js',
        'https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js',
        array(),
        '12.0.0',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'swiper_enqueue_scripts' );


// Enquenue Sanders System scripts
function theme_enqueue_scripts() {
    // Enqueue jquery script
    wp_enqueue_script(
        'jquery-3.7.1',
        'https://code.jquery.com/jquery-3.7.1.min.js',
        array(),
        wp_get_theme()->get('Version'),
        true
    );
    // Enqueue Sanders System script
    wp_enqueue_script(
        'sanders-system-2025-child-theme-script',
        get_stylesheet_directory_uri() . '/js/custom.js',
        array(),
        wp_get_theme()->get('Version'),
        true
    );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_scripts' );


// Shortcode for Global Alerts at the header
function sanders_system_global_alerts_shortcode() {
    $args = array(
        'post_type'      => 'global-alerts',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    );
    $query = new WP_Query($args);
    if (!$query->have_posts()) {
        return '';
    }
    ob_start();
    ?>
    <section id="global-alerts" class="global-alerts-section hide">
        <div class="global-alerts-container">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <div class="global-alert-item">
                    <h3 class="global-alert-title"><?php echo esc_html(get_the_title()); ?></h3>
                    <div class="global-alert-content">
                        <?php
                        $content = get_the_content();
                        $content = preg_replace('/<!--.*?-->/s', '', $content);
                        echo wp_kses_post($content);
                        ?>
                    </div>
                </div>
            <?php endwhile; ?>
            <div class="wp-block-button close-alert" id="close-alert"><a class="wp-block-button__link has-background wp-element-button" href="#">Close</a></div>
        </div>
    </section>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('sanders_system_global_alerts', 'sanders_system_global_alerts_shortcode');


// Quicklaunch side nav shortcode
function sanders_system_quicklaunch_shortcode() {
    global $post;

    // Fallback for REST/block editor: try get_queried_object()
    if ( ! is_a( $post, 'WP_Post' ) ) {
        $post = get_queried_object();
    }
    if ( defined( 'REST_REQUEST' ) && REST_REQUEST && ! is_a( $post, 'WP_Post' ) ) {
        // Show a static <ul> structure for the editor if no post context
        return '<ul class="sanders-system-quicklaunch">
            <li><h3><a href="#">Parent page</a></h3></li>
            <li class="page_item"><a href="#">Child page 1</a></li>
            <li class="page_item page_item_has_children"><a href="#">Child page 2</a>
                <ul class="children">
                    <li class="page_item"><a href="#">Sub child page 2.1</a></li>
                </ul>
            </li>
            <li class="page_item"><a href="#">Child page 3</a></li>
        </ul>';
    }
    if ( ! is_a( $post, 'WP_Post' ) ) {
        return '';
    }

    // Show structure as <pre> in block editor (REST request)
    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
        $structure = [];
        $pages = get_pages( [
            'child_of'    => $post->post_parent ? $post->post_parent : $post->ID,
            'sort_column' => 'menu_order',
        ] );
        if ( empty( $pages ) ) {
            // Show a static placeholder structure for the editor
            $placeholder = [
                'Parent page',
                '— Child page 1',
                '— Child page 2',
                '—— Sub child page 2.1',
                '— Child page 3',
            ];
            return '<pre class="quicklaunch-structure">' . esc_html( implode( "\n", $placeholder ) ) . '</pre>';
        }
        foreach ( $pages as $page ) {
            $structure[] = str_repeat( '— ', count( get_ancestors( $page->ID, 'page' ) ) ) . $page->post_title;
        }
        return '<pre class="quicklaunch-structure">' . esc_html( implode( "\n", $structure ) ) . '</pre>';
    }

    $parent_title = get_the_title($post->post_parent);
    $parent_link = get_permalink($post->post_parent);
    $string = '';

    if ( is_page() && $post->post_parent ) {
        $childpages = wp_list_pages( 'sort_column=menu_order&title_li=&child_of=' . $post->post_parent . '&echo=0' );
    } else {
        $childpages = wp_list_pages( 'sort_column=menu_order&title_li=&child_of=' . $post->ID . '&echo=0' );
    }

    if ( $childpages ) {
        $string .= '<ul class="sanders-system-quicklaunch">';
        $string .= '<li><h3><a href="' . $parent_link . '">' . $parent_title . '</a></h3></li>';
        $string .= $childpages;
        $string .= '</ul>';
    }

    return $string;
}
add_shortcode('sanders_system_quicklaunch', 'sanders_system_quicklaunch_shortcode');



// Breadcrumb shortcode
function sanders_system_breadcrumb_shortcode() {
    $items = array();
    // Home link
    $items[] = '<li class="home"><a href="' . home_url() . '">Home</a></li>';

    $post = get_queried_object();
    if ($post) {
        $ancestors = get_post_ancestors($post);
        $ancestors = array_reverse($ancestors);
        foreach ($ancestors as $ancestor) {
            $ancestor_title = get_the_title($ancestor);
            $ancestor_link = get_permalink($ancestor);
            $items[] = '<li><a href="' . $ancestor_link . '">' . $ancestor_title . '</a></li>';
        }
        $current_title = get_the_title($post);
        $items[] = '<li>' . $current_title . '</li>';
    }

    $breadcrumb = '<ul class="sanders-system-breadcrumb">';
    $count = count($items);
    foreach ($items as $i => $item) {
        $breadcrumb .= $item;
        if ($i < $count - 1) {
            $breadcrumb .= '<span class="bc-separator"><svg width="7" height="11" viewBox="0 0 7 11" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0.353516 0.353516L5.35351 5.35352L0.353514 10.3535" stroke="#FFFFFF"/></svg></span>';
        }
    }
    $breadcrumb .= '</ul>';
    return $breadcrumb;
}
add_shortcode('sanders_system_breadcrumb', 'sanders_system_breadcrumb_shortcode');



// Register Mega menu custom post type
function mega_menu_post_type() {
    register_post_type( 'mega-menu',
        array(
            'labels' => array(
                'name' => __( 'Mega menus' ),
                'singular_name' => __( 'Mega menu' ),
                'add_new' => __( 'Add mega menu' ),
                'add_new_item' => __( 'Add new mega menu' ),
                'edit_item' => __( 'Edit mega menu' ),
                'new_item' => __( 'New mega menu' ),
                'view_item' => __( 'View mega menu' ),
                'search_items' => __( 'Search mega menus' ),
                'not_found' => __( 'No mega menus found' ),
                'not_found_in_trash' => __( 'No mega menus found in Trash' ),
                'all_items' => __( 'All mega menus' ),
                'menu_name' => __( 'Mega menus' ),
                'name_admin_bar' => __( 'Mega menu' ),
            ),
            'has_archive' => true,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'mega-menu'),
            'menu_icon' => 'dashicons-menu',
            'capability_type' => 'post',
            'show_in_rest' => true,
            'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
        )
    );
}
add_action( 'init', 'mega_menu_post_type' );


// Shortcode to include both mega menu and mega menu link items, ordered by menu_order
function sanders_system_mega_menu_shortcode() {
    $args = array(
        'post_type'      => array('mega-menu', 'mega-menu-link', 'mobile-menu'),
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    );
    $query = new WP_Query($args);
    if (!$query->have_posts()) {
        return '';
    }
    $mobile_menu_items = array();
    foreach ($query->posts as $post) {
        if ($post->post_type === 'mobile-menu') {
            $mobile_menu_items[] = $post;
        }
    }
    $output = '<div class="sanders-system-mega-menu-wrapper">';
    // Render mobile menu button (if any mobile-menu items exist)
    if (!empty($mobile_menu_items)) {
        $output .= '<ul class="wp-block-navigation__container wp-block-navigation mobile-menu-items">';
        foreach ($mobile_menu_items as $post) {
            $output .= '<li id="mobile-menu-' . esc_attr($post->ID) . '" class="theme-mobile-menu-item">';
            $output .= '<button id="mobile-menu-btn-' . esc_attr($post->ID) . '" class="mega-menu-button mobile" aria-expanded="false"><span class="mega-menu-icon"><svg width="24" height="14" viewBox="0 0 24 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 1H24" stroke="#333333" stroke-width="2"/><path d="M0 7H24" stroke="#333333" stroke-width="2"/><path d="M0 13H24" stroke="#333333" stroke-width="2"/></svg></span></button>';
            $output .= '<div class="mega-menu-content"><div>' . apply_filters('the_content', $post->post_content) . '</div>';
            $output .= '<button class="theme-mega-menu-close-button"><span class="theme-mega-menu-close-label"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0.353516 0.353516L16.3535 16.3535" stroke="#ffffff"/><path d="M16.3535 0.353516L0.353515 16.3535" stroke="#ffffff"/></svg></span></button>';
            $output .= '</div>';
            $output .= '</li>';
        }
        $output .= '</ul>';
    }
    $output .= '<ul class="wp-block-navigation__container wp-block-navigation mega-menu-items">';
    foreach ($query->posts as $post) {
        if ($post->post_type === 'mega-menu') {
            $output .= '<li id="' . esc_attr($post->ID) . '" class="mega-menu-item">';
            $output .= '<button id="' . esc_attr($post->ID) . '" class="mega-menu-button" aria-expanded="false"><span class="wp-block-navigation-item__label">' . esc_html($post->post_title) . '</span><span class="mega-menu-icon"><svg width="11" height="7" viewBox="0 0 11 7" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.3535 0.353516L5.35352 5.35352L0.353515 0.353515" stroke="#333333"/></svg></span></button>';
            $output .= '<div class="mega-menu-content"><div>' . apply_filters('the_content', $post->post_content) . '</div>';
            $output .= '<button class="theme-mega-menu-close-button"><span class="theme-mega-menu-close-label"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0.353516 0.353516L16.3535 16.3535" stroke="#ffffff"/>
<path d="M16.3535 0.353516L0.353515 16.3535" stroke="#ffffff"/></svg></span></button>';
            $output .= '</div>';
            $output .= '</li>';
        } elseif ($post->post_type === 'mega-menu-link') {
            $url = trim(get_post_meta($post->ID, '_mega_menu_link_url', true));
            if ($url && strpos($url, '/') === 0 && strpos($url, '://') === false) {
                $url = home_url($url); // Concatenate site URL for relative paths
            }
            $external = get_post_meta($post->ID, '_mega_menu_link_external', true) === '1';
            $target = $external ? ' target="_blank" rel="noopener noreferrer"' : '';
            $output .= '<li id="' . esc_attr($post->ID) . '" class="mega-menu-item">';
            $output .= '<a href="' . esc_url($url) . '" class="theme-mega-menu-link"' . $target . '><span class="wp-block-navigation-item__label">' . esc_html($post->post_title) . '</span></a>';
            $output .= '</li>';
        }
    }
    $output .= '</ul>';
    $output .= '</div>';
    return $output;
}
add_shortcode('sanders_system_mega_menu', 'sanders_system_mega_menu_shortcode');


// Register Mega menu link post type (title only)
function mega_menu_link_post_type() {
    register_post_type('mega-menu-link', array(
        'labels' => array(
            'name' => __('Mega menu links'),
            'singular_name' => __('Mega menu link'),
            'add_new' => __('Add mega menu link'),
            'add_new_item' => __('Add new mega menu link'),
            'edit_item' => __('Edit mega menu link'),
            'new_item' => __('new mega menu link'),
            'view_item' => __('View mega menu link'),
            'search_items' => __('Search mega menu links'),
            'not_found' => __('No mega menu links found'),
            'not_found_in_trash' => __('No mega menu links found in Trash'),
            'all_items' => __('All mega menu links'),
            'menu_name' => __('Mega menu links'),
            'name_admin_bar' => __('Mega menu link'),
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-admin-links',
        'supports' => array('title'),
        'capability_type' => 'post',
    ));
}
add_action('init', 'mega_menu_link_post_type');

// Move Mega menu link under Mega menus as a submenu
add_action('admin_menu', function() {
    // Remove top-level menu for mega-menu-link
    remove_menu_page('edit.php?post_type=mega-menu-link');
    // Add as submenu under Mega menus
    add_submenu_page(
        'edit.php?post_type=mega-menu', // Parent slug (Mega menus menu)
        'All mega menu links',          // Page title
        'All mega menu links',          // Menu title
        'edit_pages',                   // Capability
        'edit.php?post_type=mega-menu-link' // Menu slug (links to the list table)
    );
}, 20);

// Register Mobile menu custom post type
function mobile_menu_post_type() {
    register_post_type('mobile-menu', array(
        'labels' => array(
            'name' => __('Mobile menu'),
            'singular_name' => __('Mobile menu'),
            'add_new' => __('Add mobile menu'),
            'add_new_item' => __('Add new mobile menu'),
            'edit_item' => __('Edit mobile menu'),
            'new_item' => __('New mobile menu'),
            'view_item' => __('View mobile menu'),
            'search_items' => __('Search mobile menu'),
            'not_found' => __('No mobile menu found'),
            'not_found_in_trash' => __('No mobile menu found in Trash'),
            'all_items' => __('All mobile menu'),
            'menu_name' => __('Mobile menu'),
            'name_admin_bar' => __('Mobile menu'),
        ),
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => false, // We'll add it as a submenu manually
        'menu_icon' => 'dashicons-smartphone',
        'supports' => array('title', 'editor', 'thumbnail', 'page-attributes'),
        'has_archive' => true,
        'show_in_rest' => true, // Enable block editor (Gutenberg)
        'rewrite' => array('slug' => 'mobile-menu'),
        'capability_type' => 'post',
    ));
}
add_action('init', 'mobile_menu_post_type');

// Add Mobile menu as a submenu under Mega menus
add_action('admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=mega-menu', // Parent slug (Mega menus menu)
        'Mobile menu',                  // Page title
        'Mobile menu',                  // Menu title
        'edit_pages',                   // Capability
        'edit.php?post_type=mobile-menu' // Menu slug (links to the list table)
    );
}, 21);

// Add submenu to display all Mega Menus and Mega Menu Links together (at the bottom)
add_action('admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=mega-menu',
        'All mega menus and links',
        'All menus and links',
        'edit_posts',
        'all-mega-menus-links',
        'theme_all_mega_menus_links_page'
    );
}, 99);

function theme_all_mega_menus_links_page() {
    echo '<div class="wrap"><h1>All mega menus and mega menu links</h1>';
    echo '<table class="wp-list-table widefat fixed striped posts">';
    echo '<thead><tr><th>Title</th><th>Type</th><th>Order</th><th>Edit</th></tr></thead><tbody>';
    $args = array(
        'post_type' => array('mega-menu', 'mega-menu-link'),
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'post_status' => 'any',
    );
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        foreach ($query->posts as $post) {
            $order = get_post_field('menu_order', $post->ID);
            $type = ($post->post_type === 'mega-menu') ? 'Mega Menu' : 'Mega Menu Link';
            $edit_link = get_edit_post_link($post->ID);
            echo '<tr>';
            echo '<td>' . esc_html($post->post_title) . '</td>';
            echo '<td>' . esc_html($type) . '</td>';
            echo '<td>' . esc_html($order) . '</td>';
            echo '<td><a href="' . esc_url($edit_link) . '">Edit</a></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="4">No Mega Menus or Links found.</td></tr>';
    }
    echo '</tbody></table></div>';
}

// Remove 'Add new mega menu' submenu from Mega menus
add_action('admin_menu', function() {
    global $submenu;
    if (isset($submenu['edit.php?post_type=mega-menu'])) {
        foreach ($submenu['edit.php?post_type=mega-menu'] as $k => $item) {
            if (isset($item[0]) && (stripos($item[0], 'Add new') !== false || stripos($item[0], 'Add Mega Menu') !== false)) {
                unset($submenu['edit.php?post_type=mega-menu'][$k]);
            }
        }
    }
}, 999);

// Add URL and external checkbox meta box
function mega_menu_link_add_meta_box() {
    add_meta_box(
        'mega_menu_link_url',
        'Link URL',
        'mega_menu_link_url_callback',
        'mega-menu-link',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'mega_menu_link_add_meta_box');

function mega_menu_link_url_callback($post) {
    $value = get_post_meta($post->ID, '_mega_menu_link_url', true);
    $external = get_post_meta($post->ID, '_mega_menu_link_external', true);
    echo '<label for="mega_menu_link_url">URL: </label>';
    echo '<input type="text" id="mega_menu_link_url" name="mega_menu_link_url" value="' . esc_attr($value) . '" style="width:100%">';
    echo '<p class="description">Enter a full URL (https://...) or a relative path (e.g. /about).</p>';
    echo '<label><input type="checkbox" name="mega_menu_link_external" value="1"' . checked($external, '1', false) . '> Open in new tab (external link)</label>';
}

function mega_menu_link_save_url($post_id) {
    if (array_key_exists('mega_menu_link_url', $_POST)) {
        update_post_meta(
            $post_id,
            '_mega_menu_link_url',
            esc_url_raw($_POST['mega_menu_link_url'])
        );
    }
    // Save external checkbox
    if (isset($_POST['mega_menu_link_external'])) {
        update_post_meta($post_id, '_mega_menu_link_external', '1');
    } else {
        update_post_meta($post_id, '_mega_menu_link_external', '0');
    }
}
add_action('save_post_mega-menu-link', 'mega_menu_link_save_url');

// Enable 'menu_order' support and quick edit for Mega menu links
add_action('init', function() {
    // Add 'page-attributes' support for menu_order
    add_post_type_support('mega-menu-link', 'page-attributes');
});


// Shortcode for Testimonials Swiper Slider
function sanders_system_testimonials_slideshow_shortcode() {
        $args = array(
            'post_type' => 'testimonial',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        );
        $query = new WP_Query($args);
        if (!$query->have_posts()) {
                return '';
        }
        ob_start();
        ?>
        <section class="testimonial-section" id="testimonials">
            <div class="container">
                <div class="swiper testimonial-wrapper">
                    <div class="swiper-wrapper">
                        <?php while ($query->have_posts()) : $query->the_post(); ?>
                            <div class="swiper-slide testimonial-items">
                                <div class="testimonial-img">
                                    <?php if (has_post_thumbnail()) {
                                            the_post_thumbnail('medium', array('alt' => get_the_title(), 'class' => 'testimonial-img'));
                                    } ?>
                                </div>
                                <div class="testimonial-text">
                                    <?php
                                    $content = get_the_content();
                                    // Remove block editor comments like <!-- wp:paragraph -->
                                    $content = preg_replace('/<!--.*?-->/s', '', $content);
                                    // Allow only <p> and basic formatting tags
                                    echo wp_kses($content, array(
                                        'p' => array(),
                                        'br' => array(),
                                        'strong' => array(),
                                        'em' => array(),
                                        'b' => array(),
                                        'i' => array(),
                                    ));
                                    ?>
                                </div>
                                <h3 class="testimonial-title"><?php echo esc_html(get_the_title()); ?></h3>
                                <div class="review-stars">
                                    <span class="star">★</span><span class="star">★</span><span class="star">★</span><span class="star">★</span><span class="star">★</span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </section>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
}
add_shortcode('sanders_system_testimonials_slideshow', 'sanders_system_testimonials_slideshow_shortcode');



// Shortcode for inner page Testimonials
function sanders_system_inner_testimonials_shortcode() {
        $args = array(
            'post_type'      => 'testimonial',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'tax_query'      => array(
                array(
                    'taxonomy' => 'display-location',
                    'field'    => 'slug',
                    'terms'    => 'inner-page-testimonials',
                ),
            ),
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        );
        $query = new WP_Query($args);
        if (!$query->have_posts()) {
                return '';
        }
        ob_start();
        ?>
        <section class="is-layout-flex testimonial-section" id="inner-page-testimonials">
        <?php while ($query->have_posts()) : $query->the_post(); ?>
            <div class="wp-block-column has-border-color testimonial-card">
                <div class="wp-block-column testimonial-image">
                    <div class="testimonial-img">
                        <?php if (has_post_thumbnail()) {
                            the_post_thumbnail('medium', array('alt' => get_the_title(), 'class' => 'testimonial-img'));
                        } ?>
                    </div>    
                </div>
                    <div class="wp-block-column testimonial-content">
                        <blockquote class="blockquote">
                            <div class="testimonial-text">
                                <?php
                                $content = get_the_content();
                                // Remove block editor comments like <!-- wp:paragraph -->
                                $content = preg_replace('/<!--.*?-->/s', '', $content);
                                // Allow only <p> and basic formatting tags
                                echo wp_kses($content, array(
                                    'p' => array(),
                                    'br' => array(),
                                    'strong' => array(),
                                    'em' => array(),
                                    'b' => array(),
                                    'i' => array(),
                                ));
                                ?>
                            </div>

                            <h3 class="testimonial-title"><?php echo esc_html(get_the_title()); ?></h3>

                            <div class="review-stars">
                                <span class="star">★</span><span class="star">★</span><span class="star">★</span><span class="star">★</span><span class="star">★</span>
                            </div>
                        </blockquote>
                    </div>
            </div>
        <?php endwhile; ?>
        </section>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
}
add_shortcode('sanders_system_inner_testimonials', 'sanders_system_inner_testimonials_shortcode');


// Shortcode for homepage Testimonials
function sanders_system_homepage_testimonials_shortcode() {
        // Get all terms for 'display-location' taxonomy
        $all_terms = get_terms(array(
            'taxonomy' => 'display-location',
            'hide_empty' => false,
        ));
        $matching_terms = array();
        foreach ($all_terms as $term) {
            if (strpos($term->slug, 'front-page-testimonials') !== false) {
                $matching_terms[] = $term->slug;
            }
        }
        $args = array(
            'post_type'      => 'testimonial',
            'posts_per_page' => 5,
            'post_status'    => 'publish',
            'tax_query'      => array(
                array(
                    'taxonomy' => 'display-location',
                    'field'    => 'slug',
                    'terms'    => $matching_terms,
                    'operator' => 'IN',
                ),
            ),
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        );
        $query = new WP_Query($args);
        if (!$query->have_posts()) {
                return '';
        }
        ob_start();
        ?>
        <section class="is-layout-flex testimonial-section" id="homepage-testimonials">
        <?php while ($query->have_posts()) : $query->the_post(); ?>
            <div class="wp-block-column has-border-color testimonial-card">
                <div class="wp-block-column testimonial-image">
                    <div class="testimonial-img">
                        <?php if (has_post_thumbnail()) {
                            the_post_thumbnail('medium', array('alt' => get_the_title(), 'class' => 'testimonial-img'));
                        } ?>
                    </div>    
                </div>
                    <div class="wp-block-column testimonial-content">
                        <blockquote class="blockquote">
                            <div class="testimonial-text">
                                <?php
                                $content = get_the_content();
                                // Remove block editor comments like <!-- wp:paragraph -->
                                $content = preg_replace('/<!--.*?-->/s', '', $content);
                                // Allow only <p> and basic formatting tags
                                echo wp_kses($content, array(
                                    'p' => array(),
                                    'br' => array(),
                                    'strong' => array(),
                                    'em' => array(),
                                    'b' => array(),
                                    'i' => array(),
                                ));
                                ?>
                            </div>

                            <h3 class="testimonial-title"><?php echo esc_html(get_the_title()); ?></h3>

                            <div class="review-stars">
                                <span class="star">★</span><span class="star">★</span><span class="star">★</span><span class="star">★</span><span class="star">★</span>
                            </div>
                        </blockquote>
                    </div>
            </div>
        <?php endwhile; ?>
        </section>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
}
add_shortcode('sanders_system_homepage_testimonials', 'sanders_system_homepage_testimonials_shortcode');


// Block registration for theme mega menu block
function theme_blocks_sanders_system_mega_menu_block_init() {
	if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
		wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
		return;
	}
	if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
		wp_register_block_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
	}
	$manifest_data = require __DIR__ . '/build/blocks-manifest.php';
	foreach ( array_keys( $manifest_data ) as $block_type ) {
		register_block_type( __DIR__ . "/build/{$block_type}" );
	}
}
add_action( 'init', 'theme_blocks_sanders_system_mega_menu_block_init' );


// Block registration for theme breadcrumb block
function theme_blocks_sanders_system_breadcrumb_block_init() {
	if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
		wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
		return;
	}
	if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
		wp_register_block_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
	}
	$manifest_data = require __DIR__ . '/build/blocks-manifest.php';
	foreach ( array_keys( $manifest_data ) as $block_type ) {
		register_block_type( __DIR__ . "/build/{$block_type}" );
	}
}
add_action( 'init', 'theme_blocks_sanders_system_breadcrumb_block_init' );


// Block registration for theme quicklaunch block
function theme_blocks_sanders_system_quicklaunch_block_init() {
	if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
		wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
		return;
	}
	if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
		wp_register_block_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
	}
	$manifest_data = require __DIR__ . '/build/blocks-manifest.php';
	foreach ( array_keys( $manifest_data ) as $block_type ) {
		register_block_type( __DIR__ . "/build/{$block_type}" );
	}
}
add_action( 'init', 'theme_blocks_sanders_system_quicklaunch_block_init' );


// Shortcode for Hero Swiper Slider
function sanders_system_hero_slider_shortcode() {
        $args = array(
            'post_type' => 'hero-slide',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        );
        $query = new WP_Query($args);
        if (!$query->have_posts()) {
                return '';
        }
        ob_start();
        ?>
        <section class="hero-slideshow" id="hero-slideshow">
            <div class="container">
                <div class="swiper hero-wrapper">
                    <div class="swiper-wrapper">
                        <?php while ($query->have_posts()) : $query->the_post(); ?>
                            <div class="swiper-slide hero-items">
                                <div class="hero-img">
                                    <?php if (has_post_thumbnail()) {
                                            the_post_thumbnail('medium', array('alt' => get_the_title(), 'class' => 'hero-img'));
                                    } ?>
                                </div>
                                <?php
                                    $page_url = get_field('page_url');
                                    if (!empty($page_url)) {
                                        echo '<h3 class="hero-title"><a href="' . esc_url($page_url) . '" target="_blank" rel="noopener">' . esc_html(get_the_title()) . '</a></h3>';
                                    } else {
                                        echo '<h3 class="hero-title">' . esc_html(get_the_title()) . '</h3>';
                                    }
                                ?>
                                <div class="hero-text">
                                    <?php
                                    $content = get_the_content();
                                    // Remove block editor comments like <!-- wp:paragraph -->
                                    $content = preg_replace('/<!--.*?-->/s', '', $content);
                                    // Allow only <p> and basic formatting tags
                                    echo wp_kses($content, array(
                                        'p' => array(),
                                        'br' => array(),
                                        'strong' => array(),
                                        'em' => array(),
                                        'b' => array(),
                                        'i' => array(),
                                    ));
                                    ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </section>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
}
add_shortcode('sanders_system_hero_slider', 'sanders_system_hero_slider_shortcode');




// Install Plugins listing and Activation via TGM Plugin Activation
// http://tgmpluginactivation.com/configuration/

require_once dirname( __FILE__ ) . '/class-tgm-plugin-activation.php';
add_action( 'tgmpa_register', 'sanders_system_register_required_plugins' );
function sanders_system_register_required_plugins() {
  /*
   * Array of plugin arrays. Required keys are name and slug.
   * If the source is NOT from the .org repo, then local source is also required.
   */
  $plugins = array(
   array(
      'name'     => 'All-in-One WP Migration',
      'slug'     => 'all-in-one-wp-migration',
      'required' => false,
    ),
    array( 
      'name'     => 'Contact Form 7', 
      'slug'     => 'contact-form-7', 
      'required' => false,
    ),
    array( 
      'name'     => 'Simple Custom Post Order', 
      'slug'     => 'simple-custom-post-order', 
      'required' => false,
      'force_activation'   => false,
    ),
    // array( 
    //   'name'     => 'Page Links To', 
    //   'slug'     => 'page-links-to', 
    //   'required' => false,
    // ),
    array( 
      'name'     => 'Advanced Custom Fields',
      'slug'     => 'advanced-custom-fields', 
      'required' => false,
    ),
    array( 
      'name'     => 'Advanced Media Offloader',
      'slug'     => 'advanced-media-offloader', 
      'required' => false,
    ),
    /*array( 
      'name'     => 'which template file',
      'slug'     => 'which-template-file', // The slug has to match the extracted folder from the zip.
      'source'   => get_template_directory_uri() . '/bundled-plugins/plugin-file-name.zip',
      'required' => true,
    ),*/
  );

  /*
   * Array of configuration settings.
  */
  $config = array(
    'id'           => 'tgmpa',                 // Unique ID for hashing notices for multiple instances of TGMPA.
    'default_path' => '',                      // Default absolute path to bundled plugins.
    'menu'         => 'tgmpa-install-plugins', // Menu slug.
    'parent_slug'  => 'themes.php',            // Parent menu slug.
    'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
    'has_notices'  => true,                    // Show admin notices or not.
    'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
    'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
    'is_automatic' => false,                   // Automatically activate plugins after installation or not.
    'message'      => '',                      // Message to output right before the plugins table.
    /*
    'strings'      => array(
      'page_title'                      => __( 'Install Required Plugins', 'theme-slug' ),
      'menu_title'                      => __( 'Install Plugins', 'theme-slug' ),
      // <snip>...</snip>
      'nag_type'                        => 'updated', // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
    )
    */
  );
    tgmpa( $plugins, $config );
}




