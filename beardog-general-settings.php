<?php
if (!defined('WPINC')) {
    die;
}

/*
Plugin Name: Beardog General Settings
Description: Adds general settings for BearDog theme.
Version: 1.1
Author: Suresh Dutt
*/
// Disable error display in production environment
if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
    ini_set( 'display_errors', 0 );
    error_reporting( 0 );
}
if (!defined('BEARDOG_PLUGIN_DIR')) {
    define('BEARDOG_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('BEARDOG_PLUGIN_URL')) {
    define('BEARDOG_PLUGIN_URL', plugin_dir_url(__FILE__));
}

register_activation_hook(__FILE__, 'beardog_plugin_activate');
register_deactivation_hook(__FILE__, 'beardog_plugin_deactivate');
register_uninstall_hook(__FILE__, 'beardog_plugin_uninstall');

function beardog_plugin_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'general_settings';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        copy_paste_text tinyint(1) NOT NULL DEFAULT 0,
        phone_valid_number tinyint(1) NOT NULL DEFAULT 0,
        email_valid_number tinyint(1) NOT NULL DEFAULT 0,
        convert_images_to_webp tinyint(1) NOT NULL DEFAULT 0,
        add_remote_user_ip_country tinyint(1) NOT NULL DEFAULT 0,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function beardog_plugin_deactivate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'general_settings';
    $wpdb->query("DELETE FROM $table_name");
}

function beardog_plugin_uninstall() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'general_settings';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}

function beardog_enqueue_styles() {
    wp_enqueue_style('beardog-style', BEARDOG_PLUGIN_URL . 'css/style.css');
    wp_enqueue_script('beardog-custom-js', BEARDOG_PLUGIN_URL . 'js/custom.js');
}
add_action('admin_enqueue_scripts', 'beardog_enqueue_styles');

// Add settings page to the admin menu
function beardog_settings_menu() {
    add_menu_page(
        'Beardog General Settings', // Page title
        'Beardog Settings', // Menu title
        'manage_options', // Capability required
        'beardog-general-settings', // Menu slug
        'beardog_settings_page', // Callback function
        'dashicons-screenoptions', // Icon
        30 // Position
    );
}
add_action('admin_menu', 'beardog_settings_menu');
function custom_admin_footer_script() {
    echo '<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            var settingsUpdated = document.querySelector(".updated");
            if (settingsUpdated) {
                settingsUpdated.style.display = "none";
            }
        }, 5000); // 2000 milliseconds = 2 seconds
    });
    </script>';
}
add_action('admin_footer', 'custom_admin_footer_script');

// Callback function to display settings page
function beardog_settings_page() {
    global $wpdb;

    // Check if form is submitted, then save the data
    if (isset($_POST['submit'])) {
        $copy_paste_text = isset($_POST['copy_paste_text']) ? 1 : 0;
        $phone_valid_number = isset($_POST['phone_valid_number']) ? 1 : 0;
        $email_valid_number = isset($_POST['email_valid_number']) ? 1 : 0;
        $convert_images_to_webp = isset($_POST['convert_images_to_webp']) ? 1 : 0;
        $add_remote_user_ip_country = isset($_POST['add_remote_user_ip_country']) ? 1 : 0;

        $table_name = $wpdb->prefix . 'general_settings';

        // Check if data exists in the table
        $existing_data = $wpdb->get_row("SELECT * FROM $table_name");
        if ($existing_data) {
            $wpdb->query("DELETE FROM $table_name");
        }

        // Insert new data
        $wpdb->insert(
            $table_name,
            array(
                'copy_paste_text' => $copy_paste_text,
                'phone_valid_number' => $phone_valid_number,
                'email_valid_number' => $email_valid_number,
                'convert_images_to_webp' => $convert_images_to_webp,
                'add_remote_user_ip_country' => $add_remote_user_ip_country
            )
        );

        // Retrieve the saved checkbox values
        $settings_notification = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}general_settings ORDER BY id DESC LIMIT 1");
        $html_notify = '';
        // Check if GD library is enabled
        $gdstatus = intval($settings_notification->convert_images_to_webp);
        if (!extension_loaded('gd') && !function_exists('gd_info') && $gdstatus === 1) {
            $html_notify .= '<div id="settings-saved" class="update-message notice inline notice-error notice-alt" style="background-color: #fcf0f1;padding: 10px;width: 50%;">';
            $html_notify .= '<span class="wpcf7-not-valid-tip" style="display: block;color:#d63638;font-weight:bold;margin-bottom: 10px;" aria-hidden="true">GD library is not enabled.</span>';
            $html_notify .= '</div>';
        } else {
            $html_notify .= '<div id="settings-saved" class="updated"><p><strong>Settings updated.</strong></p></div>';
        }
    }

    // Retrieve the saved checkbox values
    $settings = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}general_settings ORDER BY id DESC LIMIT 1");

    if (!$settings) {
        $settings = (object) array(
            'copy_paste_text' => 0,
            'phone_valid_number' => 0,
            'email_valid_number' => 0,
            'convert_images_to_webp' => 0,
            'add_remote_user_ip_country' => 0
        );
    }
    ?>
    <div class="form-wrapper">
        <h2 class="form-title">General Settings <img src="http://localhost/ecommerce_site/wp-content/uploads/2024/04/loader.gif" width="50px" height="50px"/></h2>
            <?php echo $html_notify;?>
            <form method="post" action="">
            <label class="form-label" for="copy_paste_text">
                <span class="switch">
                    <input type="checkbox" id="copy_paste_text" name="copy_paste_text" value="1" <?php checked(1, $settings->copy_paste_text); ?> />
                    <span class="slider round"></span>
                </span>
                <span class="checkbox-label">Prevent copy paste in textarea field</span>
            </label>
            <br>
            <label class="form-label" for="phone_valid_number">
                <span class="switch">
                    <input type="checkbox" id="phone_valid_number" name="phone_valid_number" value="1" <?php checked(1, $settings->phone_valid_number); ?> />
                    <span class="slider round"></span>
                </span>
                <span class="checkbox-label">Phone Number Validation</span>
            </label>
            <br>
            <label class="form-label" for="email_valid_number">
                <span class="switch">
                    <input type="checkbox" id="email_valid_number" name="email_valid_number" value="1" <?php checked(1, $settings->email_valid_number); ?> />
                    <span class="slider round"></span>
                </span>
                <span class="checkbox-label">Email Validation</span>
            </label>
            <br>
            <label class="form-label" for="convert_images_to_webp">
                <span class="switch">
                    <input type="checkbox" id="convert_images_to_webp" name="convert_images_to_webp" value="1" <?php checked(1, $settings->convert_images_to_webp); ?> />
                    <span class="slider round"></span>
                </span>
                <span class="checkbox-label">Convert images(jpg,jpeg,png) to .webp</span>
            </label>
            <br>
            <label class="form-label" for="add_remote_user_ip_country">
                <span class="switch">
                    <input type="checkbox" id="add_remote_user_ip_country" name="add_remote_user_ip_country" value="1" <?php checked(1, $settings->add_remote_user_ip_country); ?> />
                    <span class="slider round"></span>
                </span>
                <span class="checkbox-label">Add Remote Ip/Country on mail template</span>
            </label>
            <p style="margin-top: 50px;">
                <input type="submit" name="submit" class="hover-center-2" value="Save Settings">
            </p>
        </form>
    </div>
    <?php
}

global $wpdb;
$settings = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}general_settings ORDER BY id DESC LIMIT 1");

if (!$settings) {
    $settings = (object) array(
        'copy_paste_text' => 0,
        'phone_valid_number' => 0,
        'email_valid_number' => 0,
        'convert_images_to_webp' => 0,
        'add_remote_user_ip_country' => 0
    );
}

$copy_paste_text = intval($settings->copy_paste_text);
$phone_valid_number = intval($settings->phone_valid_number);
$email_valid_number = intval($settings->email_valid_number);
$convert_images_to_webp = intval($settings->convert_images_to_webp);
$add_remote_user_ip_country = intval($settings->add_remote_user_ip_country);

if ($copy_paste_text === 1) {
    include plugin_dir_path(__FILE__) . 'inc/copy_paste_text_functions.php';
}

if ($phone_valid_number === 1) {
    include plugin_dir_path(__FILE__) . 'inc/phone_validation_functions.php';
}

if ($email_valid_number === 1) {
    include plugin_dir_path(__FILE__) . 'inc/email_validation_functions.php';
}

if ($convert_images_to_webp === 1) {
    include plugin_dir_path(__FILE__) . 'inc/convert_images_functions.php';
}

if ($add_remote_user_ip_country === 1) {
    include plugin_dir_path(__FILE__) . 'inc/add_remote_user_ip_country.php';
}
