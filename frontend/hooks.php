<?php

/**
 * Copyright (c) Author <contact@website.com>
 *
 * This source code is licensed under the license
 * included in the root directory of this application.
 */

/**
 * Include popup
 */
add_action('wp_footer', function () {
	require META_LOCKER_DIR . 'frontend/templates/popup.php';
});

/**
 * Print callout appearance CSS
 */
function metalocker_print_callout_css()
{
	$settings = array_merge(
		array(
			'bg_color'              => 'rgba(209, 228, 221, 1)',
			'button_color'          => 'rgba(209, 228, 221, 1)',
			'button_bg_color'       => 'rgba(40, 48, 61, 1)',
			'button_bg_hover_color' => 'rgba(209, 228, 221, 1)',
			'input_bg_color'        => 'rgba(209, 228, 221, 1)',
			'button_hover_color'    => 'rgba(40, 48, 61, 1)',
			'message_color'         => 'rgba(40, 48, 61, 1)',
			'input_color'           => 'rgba(40, 48, 61, 1)',
			'button_text_size'      => 18,
			'button_border_size'    => 3,
			'message_text_size'     => 24,
			'button_padding_x'      => 15,
			'button_padding_y'      => 30,
		),
		(array) get_option('metaLockerSettings')
	);

	echo '<style type="text/css">
    :root {
        --metalocker-bg-color:' . $settings['bg_color'] . ';
        --metalocker-bg-color-0:' . substr($settings['bg_color'], 0, -2) . '0);
        --metalocker-input-color:' . $settings['input_color'] . ';
        --metalocker-button-color:' . $settings['button_color'] . ';
        --metalocker-message-color:' . $settings['message_color'] . ';
        --metalocker-input-bg-color:' . $settings['input_bg_color'] . ';
        --metalocker-button-bg-color:' . $settings['button_bg_color'] . ';
        --metalocker-button-padding-x:' . $settings['button_padding_x'] . 'px;
        --metalocker-button-padding-y:' . $settings['button_padding_y'] . 'px;
        --metalocker-button-text-size:' . $settings['button_text_size'] . 'px;
        --metalocker-message-text-size:' . $settings['message_text_size'] . 'px;
        --metalocker-button-border-size:' . $settings['button_border_size'] . 'px;
        --metalocker-button-hover-color:' . $settings['button_hover_color'] . ';
        --metalocker-button-hover-bg-color:' . $settings['button_bg_hover_color'] . ';
    }
    </style>';
}
add_action('wp_head', 'metalocker_print_callout_css', PHP_INT_MAX);

/**
 * Enqueue scripts
 */
function metalocker_on_wp_enqueue_scripts()
{
	$settings = (array) get_option('metaLockerSettings');

	$symbols = require META_LOCKER_DIR . 'assets/symbols.php';
	$testnets = require META_LOCKER_DIR . 'assets/testnets.php';
	wp_enqueue_style('meta-locker', META_LOCKER_URI . 'assets/css/frontend.min.css', [], META_LOCKER_VER);

	wp_enqueue_script('meta-locker', META_LOCKER_URI . 'assets/js/frontend.min.js', [], META_LOCKER_VER, true);
    wp_enqueue_script('meta-locker', META_LOCKER_URI . 'assets/js/components/metalocker.js', [], META_LOCKER_VER, true);
    wp_enqueue_script('meta-locker', META_LOCKER_URI . 'assets/js/components/LazyScriptsLoader.js', [], META_LOCKER_VER, true);
    wp_enqueue_script('meta-locker', META_LOCKER_URI . 'assets/js/admin.min.js', [], META_LOCKER_VER, true);
    wp_localize_script('meta-locker', 'networkInfo', array('symbols' => $symbols, 'testnets' => $testnets));  // Localize the first script

	wp_enqueue_script('metalocker-bundle', META_LOCKER_URI . 'assets/js/bundle.min.js', [], META_LOCKER_VER, true);

	wp_localize_script(
		'meta-locker',
		'metaLocker',
		array(
			'nonce'         => wp_create_nonce('m3t4L0k3r'),
			'ajaxURL'       => admin_url('admin-ajax.php'),
			'pluginVer'     => META_LOCKER_VER,
			'pluginUri'     => META_LOCKER_URI,
			'infuraKey'     => 'e7cdb73a875e4f33b04a8e5488a620f4',
			'solanaCluster' => 'mainnet-beta',
			'settings'      => array_merge(
				array(
					'name'            => 'MetaLocker',
					'button_text'     => __('Connect Wallet', 'meta-locker'),
					'message'         => __('To read the full content, please enter your email address and connect your wallet!', 'meta-locker'),
					'minimum_balance' => 0.0001,
					'balance_message' => __('Sorry, insufficient balance!', 'meta-locker')
				),
				$settings
			),
		)
	);

	wp_localize_script(
		'meta-locker',
		'metaLockerI18n',
		array(
			'unknowErr' => __('Unknown error occured. Please try again!', 'meta-locker'),
			'serviceErr' => __('Service unavailable! Please try again!', 'meta-locker'),
			'balanceErr' => __('Sorry, insufficient balance!', 'meta-locker'),
			'invalidEmail' => __('Invalid email address. Please correct the email!', 'meta-locker'),
			'emptyEmail' => __('Please enter your mail address!', 'meta-locker'),
			'consentText' => __('You must agree to our Privacy Policy!', 'meta-locker'),
		)
	);
}
add_action('wp_enqueue_scripts', 'metalocker_on_wp_enqueue_scripts');

/**
 * Maybe show the callout
 */
function metalocker_maybe_show_callout($content)
{
	global $wp_query;

	if (defined('REST_REQUEST') || !$wp_query->is_main_query() || 'post' !== $wp_query->post->post_type || current_user_can('edit_posts')) {
		return $content;
	}

	if (get_post_meta($wp_query->post->ID, 'metaLockerDisabled', true)) {
		return $content;
	}

	$crawler_detector = new Jaybizzle\CrawlerDetect\CrawlerDetect();

	if (empty($_COOKIE['isValidMetaUser']) && !$crawler_detector->isCrawler()) {
		$content = trim($content);
		$settings = array_merge(
			array(
				'message'        => __('To read the full content, please enter your email address and connect your wallet!', 'meta-locker'),
				'button_text'    => __('Connect Wallet', 'meta-locker'),
				'consent_text'   => __('I agree to the Privacy Policy.', 'meta-locker'),
				'excerpt_length' => 150,
				'disable_auto_insert' => false,
				'checkbox_consent_state' => 0,
				'privacy_policy_url' => get_privacy_policy_url(),
			),
			(array) get_option('metaLockerSettings')
		);
		$class_name = 'metaLocker';
		preg_match('/<div\sclass="meta-locker-callout.+/s', $content, $matches);
		if (!empty($matches[0])) {
			$position = strpos($content, '<div class="meta-locker-callout');
			$content  = str_replace($matches[0], '', $content);
			if (0 === $position) {
				$class_name .= ' hide-backdrop';
			}
			if (false !== strpos($matches[0], 'meta-locker-callout hide-email')) {
				$class_name .= ' hide-email';
			}
		} else {
			if ($settings['disable_auto_insert']) {
				return $content;
			} else {
				$content = '<p>' . substr(wp_strip_all_tags($content, true), 0, $settings['excerpt_length']) . '...</p>';
			}
		}
		$checked = $settings['checkbox_consent_state'] ? ' checked' : '';
		$content .= '<div class="' . $class_name . '"><div class="metaLockerMask"></div>
            <h3 class="metaLockerMessage">' . $settings['message'] . '</h3>
            <input class="metaLockerEmail" type="email" placeholder="Email..." />
            <button class="metaLockerConnect">' . $settings['button_text'] . '</button>
			<p class="metaLockerTick"><label><input type="checkbox" value="1"' . $checked . '><a href="' . $settings['privacy_policy_url'] . '" target="_blank">' . $settings['consent_text'] . '</a></label></p></div>';
	}

	return $content;
}
add_filter('the_content', 'metalocker_maybe_show_callout', PHP_INT_MAX);
