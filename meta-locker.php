<?php

/**
 * Plugin Name: Meta Locker
 * Description: A content locker WordPress plugin. Users are required to login with their crypto wallet to view the locked content.
 * Author: adastracrypto.com
 * Author URI: https://adastracrypto.com
 * Version: 1.2.2
 */


define('META_LOCKER_VER', '1.2.2');
define('META_LOCKER_DIR', __DIR__ . '/');
define('META_LOCKER_URI', plugins_url('/', __FILE__));
define('LOCKER_PLUGIN', "metalocker");
define('LOCKER_TABLE', "metalocker_sessions");
// Load vendor resources.
require __DIR__ . '/vendor/autoload.php';

// Load common resources.
require __DIR__ . '/common/class-rest-api.php';
require __DIR__ . '/common/functions.php';
require __DIR__ . '/common/hooks.php';

/**
 * Do activation
 *
 * @see https://developer.wordpress.org/reference/functions/register_activation_hook/
 *
 * @param bool $network Being activated on multisite or not.
 * @throws Exception
 */
function metalocker_activate($network)
{
	global $wpdb;

	try {
		if (version_compare(PHP_VERSION, '7.2', '<')) {
			throw new Exception(__('This plugin requires PHP version 7.2 at least!', 'meta-locker'));
		}

		if (!get_option('metaLockerActivated') && !get_transient('metalocker_init_activation') && !set_transient('metalocker_init_activation', 1)) {
			throw new Exception(__('Failed to initialize setup wizard.', 'meta-locker'));
		}

		if (!function_exists('dbDelta')) {
			require ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		$wpdb->query('DROP TABLE IF EXISTS metalocker_sessions;');

		dbDelta("CREATE TABLE IF NOT EXISTS metalocker_sessions (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			ip VARCHAR(32) NOT NULL DEFAULT '',
			agent VARCHAR(512) NOT NULL DEFAULT '',
			link VARCHAR(255) NOT NULL DEFAULT '',
			email VARCHAR(126) NOT NULL DEFAULT '',
			balance VARCHAR(32) NOT NULL DEFAULT '',
			wallet_type VARCHAR(16) NOT NULL DEFAULT '0',
			wallet_address VARCHAR(126) NOT NULL DEFAULT '',
			visited_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			synced TINYINT DEFAULT 0,
			PRIMARY KEY  (id)
		);");
		$wpdb->query('CREATE TABLE IF NOT EXISTS meta_wallet_connections (
				id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				plugin_name VARCHAR(255) NOT NULL,
				session_table VARCHAR(255) NOT NULL,
				session_id INT NOT NULL,
				wallet_address VARCHAR(126) NOT NULL,
				ticker VARCHAR(16) NOT NULL,
				wallet_type VARCHAR(16) NOT NULL
			)');

		MetaLockerRestApi::setupKeypair();


		if (!wp_next_scheduled('metalocker_sync_data')) {
			if (!wp_schedule_event(time(), 'every_sixty_minutes', 'metalocker_sync_data')) {
				throw new Exception(__('Failed to connect to remote server!', 'meta-locker'));
			}
		}
	} catch (Exception $e) {
		if (defined('DOING_AJAX') && DOING_AJAX) {
			header('Content-Type: application/json; charset=' . get_option('blog_charset'), true, 500);
			exit(wp_json_encode([
				'success' => false,
				'name' => __('Plugin Activation Error', 'meta-locker'),
				'message' => $e->getMessage(),
			]));
		} else {
			exit($e->getMessage());
		}
	}
}
add_action('activate_meta-locker/meta-locker.php', 'metalocker_activate');

function run_every_sixty_minutes($schedules)
{
	$schedules['every_sixty_minutes'] = array(
		'interval' => 3600,
		'display' => __('Every 60 Minutes', 'textdomain')
	);
	return $schedules;
}

add_filter('cron_schedules', 'run_every_sixty_minutes');

/**
 * Do installation
 *
 * @see https://developer.wordpress.org/reference/hooks/plugins_loaded/
 */
function metalocker_install()
{
	// Make sure translation is available.
	load_plugin_textdomain('meta-locker', false, 'meta-locker/languages');

	// Load resources.
	if (is_admin()) {
		if (!class_exists('WP_List_Table')) {
			require ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}
		if (!function_exists('deactivate_plugins')) {
			require ABSPATH . 'wp-admin/includes/plugin.php';
		}
		require __DIR__ . '/admin/class-terms-page.php';
		require __DIR__ . '/admin/class-settings-page.php';
		require __DIR__ . '/admin/class-license-manager.php';
		require __DIR__ . '/admin/hooks.php';
	} else {
		require __DIR__ . '/frontend/hooks.php';
	}
}
add_action('plugins_loaded', 'metalocker_install', 10, 0);
/*
|--------------------------------------------------------------------------
|  admin noticce for add infura project key
|--------------------------------------------------------------------------
 */

function meta_admin_notice_warn()
{
	$settings = (array) get_option('metaLockerSettings');

	if (!isset($settings['infura_project_id']) && empty($settings['infura_project_id'])) {
		echo '<div class="notice notice-error is-dismissible">
        <p>Important:Please enter an infura API-KEY for WalletConnect to work <a style="font-weight:bold" href="' . esc_url(get_admin_url(null, 'admin.php?page=metalocker-settings')) . '">Link</a></p>
        </div>';
	}
}
if (is_admin()) {
	add_action('admin_notices', 'meta_admin_notice_warn');
}