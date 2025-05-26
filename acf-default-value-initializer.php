<?php
/**
 * Plugin Name: ACF Default Value Initializer
 * Plugin URI: https://github.com/mituu-rs/acf-default-value-initializer
 * Description: Automatically initialize default values for ACF fields on existing posts and users
 * Version: 1.0.0
 * Author: mituu
 * Author URI: https://mituu.dev
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Text Domain: acf-default-value-initializer
 * Domain Path: /languages
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ACF_DVI_VERSION', '1.0.0');
define('ACF_DVI_PLUGIN_FILE', __FILE__);
define('ACF_DVI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ACF_DVI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ACF_DVI_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Check if ACF (free or Pro) is active
 */
function acf_dvi_is_acf_active(): bool
{
    // Check for ACF Pro
    if (class_exists('acf_pro')) {
        return true;
    }

    // Check for ACF Free
    if (class_exists('ACF')) {
        return true;
    }

    // Check if acf function exists (most reliable method)
    if (function_exists('acf')) {
        return true;
    }

    // Check if ACF is in active plugins
    if (in_array('advanced-custom-fields/acf.php', get_option('active_plugins', []), true)) {
        return true;
    }

    // Check if ACF Pro is in active plugins
    if (in_array('advanced-custom-fields-pro/acf.php', get_option('active_plugins', []), true)) {
        return true;
    }

    // Check for multisite
    if (is_multisite()) {
        $active_plugins = get_site_option('active_sitewide_plugins', []);
        if (isset($active_plugins['advanced-custom-fields/acf.php']) ||
            isset($active_plugins['advanced-custom-fields-pro/acf.php'])) {
            return true;
        }
    }

    return false;
}

/**
 * Display ACF missing notice
 */
function acf_dvi_missing_acf_notice(): void
{
    echo '<div class="notice notice-error"><p>';
    echo esc_html__('ACF Default Value Initializer requires Advanced Custom Fields (free or Pro) to be installed and active.', 'acf-default-value-initializer');
    echo '</p></div>';
}

// Load Composer autoloader if it exists
if (file_exists(ACF_DVI_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once ACF_DVI_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    // Manual autoloader for non-Composer installations
    spl_autoload_register(function (string $class): void {
        $prefix = 'Mituu\\ACFDefaultValueInitializer\\';
        $base_dir = ACF_DVI_PLUGIN_DIR . 'src/';

        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require $file;
        }
    });
}

use Mituu\ACFDefaultValueInitializer\Plugin;

// Initialize the plugin with proper ACF detection
add_action('plugins_loaded', function (): void {
    if (!acf_dvi_is_acf_active()) {
        add_action('admin_notices', 'acf_dvi_missing_acf_notice');
        return;
    }

    Plugin::getInstance()->init();
}, 20); // Priority 20 to ensure ACF is loaded first

// Activation hook with improved ACF detection
register_activation_hook(__FILE__, function (): void {
    // We can't reliably check for ACF during activation since plugins might not be loaded yet
    // So we'll defer this check to the plugins_loaded hook above
});

// Deactivation hook
register_deactivation_hook(__FILE__, function (): void {
    // No cleanup needed
});