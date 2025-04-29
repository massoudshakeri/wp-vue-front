<?php
/**
 * Plugin Name: WP-Vue-Front
 * Description: A WordPress plugin with Vue.js integration for managing accounting, suppliers, products, and team.
 * Version: 1.0.0
 * Author: Massoud Shakeri
 * Author URI: https://blazingspider.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WP_VUE_FRONT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WP_VUE_FRONT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Add menu item to WordPress dashboard
function wp_vue_front_add_menu() {
    add_menu_page(
        'WP-Vue-Front', 
        'WP-Vue-Front', 
        'manage_options', 
        'wp-vue-front', 
        'wp_vue_front_main_page',
        'dashicons-admin-generic'
    );
}
add_action('admin_menu', 'wp_vue_front_add_menu', 100);

// Enqueue necessary scripts and styles
function wp_vue_front_enqueue_scripts() {
    $screen = get_current_screen();
    if ($screen->id !== 'toplevel_page_wp-vue-front') {
        return;
    }

    // Enqueue the Vite development server script in development
    if (defined('WP_DEBUG') && WP_DEBUG) {
        wp_enqueue_script(
            'wp-vue-front-vue',
            'http://localhost:5173/@vite/client',
            [],
            null,
            true
        );
        wp_enqueue_script(
            'wp-vue-front-vue-app',
            'http://localhost:5173/src/vue-loader.js',
            ['wp-vue-front-vue'],
            null,
            true
        );
        // Add type="module" to both scripts
        add_filter('script_loader_tag', function($tag, $handle) {
            if ($handle === 'wp-vue-front-vue' || $handle === 'wp-vue-front-vue-app') {
                return str_replace(' src', ' type="module" src', $tag);
            }
            return $tag;
        }, 10, 2);
    } else {
        // In production, use the built files
        wp_enqueue_script('wp-vue-front-vue', WP_VUE_FRONT_PLUGIN_URL . '/assets/dist/loader.bundle.js', [], '1.0.0', true);
        wp_enqueue_style('wp-vue-front-vue', WP_VUE_FRONT_PLUGIN_URL . '/assets/dist/style.css', [], '1.0.0');
    }
}
add_action('admin_enqueue_scripts', 'wp_vue_front_enqueue_scripts');

// Main page content
function wp_vue_front_main_page() {
    $tabs = [
        'accounting' => 'Accounting',
        'suppliers' => 'Suppliers',
        'products' => 'Products',
        'team' => 'Team'
    ];
    
    $active_tab = isset($_GET['tab']) && array_key_exists($_GET['tab'], $tabs) 
        ? $_GET['tab'] 
        : 'accounting';
    ?>
    <div class="wrap">
        <h1>WP-Vue-Front Management</h1>
        
        <nav class="nav-tab-wrapper">
            <?php foreach ($tabs as $tab_id => $tab_name): ?>
                <a href="?page=wp-vue-front&tab=<?php echo $tab_id; ?>" 
                   class="nav-tab <?php echo ($active_tab === $tab_id) ? 'nav-tab-active' : ''; ?>">
                    <?php echo $tab_name; ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="tab-content">
            <?php require_once WP_VUE_FRONT_PLUGIN_PATH . 'admin-view/' . $active_tab . '.php'; ?>
            <?php switch ($_GET['tab']) {
                case 'accounting':
                    require_once WP_VUE_FRONT_PLUGIN_PATH . 'admin-view/accounting.php';
                    wp_vue_front_accounting_admin_page();
                    break;
                case 'suppliers':
                    require_once WP_VUE_FRONT_PLUGIN_PATH . 'admin-view/suppliers.php';
                    wp_vue_front_suppliers_admin_page();
                    break;
                case 'products':
                    require_once WP_VUE_FRONT_PLUGIN_PATH . 'admin-view/products.php';
                    wp_vue_front_products_admin_page();
                    break;
                case 'team':
                    require_once WP_VUE_FRONT_PLUGIN_PATH . 'admin-view/team.php';
                    wp_vue_front_team_admin_page();
                    break;
                default:
                    break;
            } ?>
        </div>
    </div>
    <?php
}

// Register AJAX handlers for each component
function wp_vue_front_register_ajax_handlers() {
    add_action('wp_ajax_wp_vue_front_accounting', 'wp_vue_front_handle_accounting');
    add_action('wp_ajax_wp_vue_front_suppliers', 'wp_vue_front_handle_suppliers');
    add_action('wp_ajax_wp_vue_front_products', 'wp_vue_front_handle_products');
    add_action('wp_ajax_wp_vue_front_team', 'wp_vue_front_handle_team');
}
add_action('init', 'wp_vue_front_register_ajax_handlers');

// Include AJAX handlers
require_once WP_VUE_FRONT_PLUGIN_PATH . 'includes/post-handler-accounting.php';
require_once WP_VUE_FRONT_PLUGIN_PATH . 'includes/post-handler-suppliers.php';
require_once WP_VUE_FRONT_PLUGIN_PATH . 'includes/post-handler-products.php';
require_once WP_VUE_FRONT_PLUGIN_PATH . 'includes/post-handler-team.php';
