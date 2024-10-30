<?php

/**
 * Copyright (c) Author <contact@website.com>
 *
 * This source code is licensed under the license
 * included in the root directory of this application.
 */

/**
 * Handle AJAX activation request
 */
function metalocker_activate_site()
{
	if (empty($_POST['email']) || empty($_POST['plugin'])) {
		exit(json_encode([
			'success' => false,
			'message' => __('Please enter your email address!', 'meta-locker')
		]));
	}
	MetaLockerRestApi::setupKeypair();

	$email = sanitize_email($_POST['email']);
	$plugin = sanitize_title($_POST['plugin']);
	$address = sanitize_text_field($_POST['address']);
	$ticker = sanitize_text_field($_POST['ticker']);
	$status = MetaLockerRestApi::getActivationStatus($plugin);

	if (!$status) {
		$status = MetaLockerRestApi::registerSite($address,$plugin, $email, $ticker);
		sleep(1);
		if ($status) {
			if ($status === 'registered') {
				
				exit(json_encode([
					'success' => true,
					'message' => __('The plugin has been activated successfully!', 'meta-locker')
				]));
			//wip no authentication email being sent
			// } else {
				
			// 	exit(json_encode([
			// 		'success' => true,
			// 		'message' => __('Please check your email for the activation link!', 'meta-locker')
			// 	]));
			}
		} else {
			exit(json_encode([
				'success' => false,
				'message' => __('Failed to activate the plugin. Please try again!', 'meta-locker')
			]));
		}
	} else {
		if ($status === 'registered') {
			
			exit(json_encode([
				'success' => true,
				'message' => __('The plugin has been activated successfully!', 'meta-locker')
			]));
		//wip no authentication email being sent
		// } else {
			
		// 	exit(json_encode([
		// 		'success' => true,
		// 		'message' => __('Please check your email for the activation link!', 'meta-locker')
		// 	]));
		}
	}
}
add_action('wp_ajax_metalocker_activate_site', 'metalocker_activate_site');

/**
 * Enqueue editor scripts
 */
function metalocker_on_enqueue_block_editor_assets()
{
	wp_enqueue_script('metalocker-sidebar-plugin', META_LOCKER_URI . 'assets/js/block-sidebar-plugin.min.js', array('wp-blocks', 'wp-element', 'wp-components'), META_LOCKER_VER, true);
}
add_action('enqueue_block_editor_assets', 'metalocker_on_enqueue_block_editor_assets');
