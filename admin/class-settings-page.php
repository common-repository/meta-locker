<?php

/**
 * Settings Page
 *
 * @package MetaLocker\Admin
 */

/**
 * MetaLocker_Settings_Page
 *
 * Displaying the Settings Page
 */
final class MetaLocker_Settings_Page
{
	/**
	 * @var string
	 */
	const SETTINGS_GROUP = 'metaLockerSettingsGroup';

	/**
	 * @var array
	 */
	private $settings;

	/**
	 * Singleton
	 */
	public static function init()
	{
		static $self = null;

		if (null === $self) {
			$self = new self;
			add_action('admin_menu', array($self, 'add_menu_page'));
			add_action('admin_init', array($self, 'register_setting_group'), 10, 0);
			add_action('admin_enqueue_scripts', array($self, 'enqueueScripts'));
		}
	}

	/**
	 * Constructor
	 */
	private function __construct()
	{
		$this->settings = array_merge(
			array(
				'bg_color'              => 'rgba(209, 228, 221, 1)',
				'button_color'          => 'rgba(209, 228, 221, 1)',
				'button_bg_color'       => 'rgba(40, 48, 61, 1)',
				'button_bg_hover_color' => 'rgba(209, 228, 221, 1)',
				'input_bg_color'        => 'rgba(209, 228, 221, 1)',
				'button_hover_color'    => 'rgba(40, 48, 61, 1)',
				'message_color'         => 'rgba(40, 48, 61, 1)',
				'input_color'           => 'rgba(40, 48, 61, 1)',
				'name'                  => 'MetaLocker',
				'button_text'           => __('Connect Wallet', 'meta-locker'),
				'button_text_size'      => 18,
				'button_border_size'    => 3,
				'message_text_size'     => 24,
				'button_padding_x'      => 15,
				'button_padding_y'      => 30,
				'message'               => __('To read the full content, please enter your email address and connect your wallet!', 'meta-locker'),
				'balance_message'       => __('Sorry, insufficient balance!', 'meta-locker'),
				'cookie_duration'       => 48,
				'excerpt_length'        => 150,
				'consent_text'          => __('I agree to the Privacy Policy.', 'meta-locker'),
				'checkbox_consent_state' => '',
				'disable_auto_insert' => '',
				'privacy_policy_url' => 'https://www.adastracrypto.com/privacy-policy',
				'receiver_wallet' => '',
				'solana_receiver_wallet' => '',
				'charge_amount' => 0,
				'solana_charge_amount' => 0,
				'minimum_balance' => 0.0001,
			),
			(array) get_option('metaLockerSettings')
		);
	}

	/**
	 * Add page
	 *
	 * @see https://developer.wordpress.org/reference/hooks/admin_menu/
	 */
	public function add_menu_page()
	{
		$this->hook_name = add_submenu_page('metalocker-tos', __('Settings', 'meta-locker'), __('Settings', 'meta-locker'), 'manage_options', 'metalocker-settings', array($this, 'render'));
	}

	/**
	 * Register setting group
	 *
	 * @internal Used as a callback
	 */
	public function register_setting_group()
	{
		register_setting(self::SETTINGS_GROUP, 'metaLockerSettings', array($this, 'sanitize'));
	}

	/**
	 * Sanitize form data
	 *
	 * @internal Used as a callback
	 * @var array $data Submiting data
	 */
	public function sanitize(array $data)
	{
		if (!empty($data['icon'])) {
			$data['icon'] = sanitize_text_field($data['icon']);
		}

		if (!empty($data['name'])) {
			$data['name'] = sanitize_text_field($data['name']);
		}

		if (!empty($data['message'])) {
			$data['message'] = sanitize_text_field($data['message']);
		}

		if (!empty($data['button_text'])) {
			$data['button_text'] = sanitize_text_field($data['button_text']);
		}

		if (!empty($data['checkbox_consent_state'])) {
			$data['checkbox_consent_state'] = absint($data['checkbox_consent_state']);
		}

		if (!empty($data['receiver_wallet'])) {
			$data['receiver_wallet'] = sanitize_text_field($data['receiver_wallet']);
		}

		if (!empty($data['charge_amount'])) {
			$data['charge_amount'] = floatval($data['charge_amount']);
		}

		if (!empty($data['minimum_balance'])) {
			$data['minimum_balance'] = floatval($data['minimum_balance']);
		}

		return $data;
	}

	/**
	 * Render the settings page
	 *
	 * @internal  Callback.
	 */
	public function render($page_data)
	{
?>
		<div class="wrap">
			<h1><?= __('MetaLocker Settings', 'meta-locker'); ?></h1>
			<form method="post" action="options.php" novalidate="novalidate">
				<?php settings_fields(self::SETTINGS_GROUP); ?>
				<div class="settings-tab">
					<table class="form-table">
						<tr>
							<th scope="row"><?= __('Infura Project API-Key', 'meta-locker'); ?></th>
							<td>
								<input style="width:300px" type="text" name="<?= $this->get_name('infura_project_id') ?>" value="<?= $this->get_value('infura_project_id') ?>">
								<p class="description"><?= __('Get your infura project API-KEY by signing up  <a href="https://infura.io/register" target="_blank"> here</a>. Choose <b>Web3 API</b> as <b>network</b> and give a nice <b>name</b> of your choice. Copy the API-KEY from the next window. ', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Paid Mode', 'meta-locker'); ?></th>
							<td>
								<input type="checkbox" name="<?= $this->get_name('paid_mode') ?>" value="1" <?php checked($this->get_value('paid_mode'), 1) ?>>
								<span class="description"><?= __('Users have to pay to view locked content.', 'meta-locker') ?></span>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('ETH Charge Amount', 'meta-locker'); ?></th>
							<td>
								<input style="width:300px" type="number" name="<?= $this->get_name('charge_amount') ?>" value="<?= $this->get_value('charge_amount') ?>">
								<p class="description"><?= __('The amount users have to pay.', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('ETH Receiver Address', 'meta-locker'); ?></th>
							<td>
								<input style="width:300px" type="text" name="<?= $this->get_name('receiver_wallet') ?>" value="<?= $this->get_value('receiver_wallet') ?>">
								<p class="description"><?= __('The wallet address where users&#8217; payments will be sent to.', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Solana Charge Amount', 'meta-locker'); ?></th>
							<td>
								<input style="width:300px" type="number" name="<?= $this->get_name('solana_charge_amount') ?>" value="<?= $this->get_value('solana_charge_amount') ?>"> <a href="https://docs.solana.com/introduction#what-are-sols" target="_blank">lamports</a>
								<p class="description">1 lamport = 0.000000001 SOL</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Solana Receiver Address', 'meta-locker'); ?></th>
							<td>
								<input style="width:300px" type="text" name="<?= $this->get_name('solana_receiver_wallet') ?>" value="<?= $this->get_value('solana_receiver_wallet') ?>">
								<p class="description"><?= __('The Solana wallet address where users&#8217; payments will be sent to.', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Message Color', 'meta-locker'); ?></th>
							<td>
								<input type="text" class="meta-locker-pick-message-color" data-owner="metaLockerMessageColor">
								<input id="metaLockerMessageColor" name="<?= $this->get_name('message_color'); ?>" type="hidden" value="<?= $this->get_value('message_color'); ?>">
								<p class="description"><?= __('Text color of the callout message.', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Message Font Size', 'meta-locker'); ?></th>
							<td>
								<input name="<?= $this->get_name('message_text_size'); ?>" type="number" value="<?= $this->get_value('message_text_size'); ?>" placeholder=""> px
								<p class="description"><?= __('Text size of the heading message.', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Background Color', 'meta-locker'); ?></th>
							<td>
								<input type="text" class="meta-locker-pick-bg-color" data-owner="metaLockerBgColor">
								<input id="metaLockerBgColor" name="<?= $this->get_name('bg_color'); ?>" type="hidden" value="<?= $this->get_value('bg_color'); ?>">
								<p class="description"><?= __('Background color of the whole callout.', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Email Input Text Color', 'meta-locker'); ?></th>
							<td>
								<input type="text" class="meta-locker-pick-input-color" data-owner="metaLockerInputColor">
								<input id="metaLockerInputColor" name="<?= $this->get_name('input_color'); ?>" type="hidden" value="<?= $this->get_value('input_color'); ?>">
								<p class="description"><?= __('Text color of the email input field.', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Email Input Background Color', 'meta-locker'); ?></th>
							<td>
								<input type="text" class="meta-locker-pick-input-bg-color" data-owner="metaLockerInputBgColor">
								<input id="metaLockerInputBgColor" name="<?= $this->get_name('input_bg_color'); ?>" type="hidden" value="<?= $this->get_value('input_bg_color'); ?>">
								<p class="description"><?= __('Background color of the email input field.', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Connect Button Color', 'meta-locker'); ?></th>
							<td>
								<input type="text" class="meta-locker-pick-button-color" data-owner="metaLockerButtonColor">
								<input id="metaLockerButtonColor" name="<?= $this->get_name('button_color'); ?>" type="hidden" value="<?= $this->get_value('button_color'); ?>">
								<p class="description"><?= __('Text color of the connect button.', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Connect Button Hover Color', 'meta-locker'); ?></th>
							<td>
								<input type="text" class="meta-locker-pick-button-hover-color" data-owner="metaLockerButtonHoverColor">
								<input id="metaLockerButtonHoverColor" name="<?= $this->get_name('button_hover_color'); ?>" type="hidden" value="<?= $this->get_value('button_hover_color'); ?>">
								<p class="description"><?= __('Text color of the connect button on hover.', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Connect Button Background Color', 'meta-locker'); ?></th>
							<td>
								<input type="text" class="meta-locker-pick-button-bg-color" data-owner="metaLockerButtonBgColor">
								<input id="metaLockerButtonBgColor" name="<?= $this->get_name('button_bg_color'); ?>" type="hidden" value="<?= $this->get_value('button_bg_color'); ?>">
								<p class="description"><?= __('Background color of the connect button.', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Connect Button Background Hover Color', 'meta-locker'); ?></th>
							<td>
								<input type="text" class="meta-locker-pick-button-bg-hover-color" data-owner="metaLockerButtonBgHoverColor">
								<input id="metaLockerButtonBgHoverColor" name="<?= $this->get_name('button_bg_hover_color'); ?>" type="hidden" value="<?= $this->get_value('button_bg_hover_color'); ?>">
								<p class="description"><?= __('Background color of the connect button on hover.', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Connect Button Text', 'meta-locker'); ?></th>
							<td>
								<input name="<?= $this->get_name('button_text'); ?>" type="text" value="<?= $this->get_value('button_text'); ?>" placeholder="">
								<p class="description"><?= __('Text of the connect button.', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Connect Button Font Size', 'meta-locker'); ?></th>
							<td>
								<input name="<?= $this->get_name('button_text_size'); ?>" type="number" value="<?= $this->get_value('button_text_size'); ?>" placeholder=""> px
								<p class="description"><?= __('Will be applied to email input too.', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Connect Button Border Width', 'meta-locker'); ?></th>
							<td>
								<input name="<?= $this->get_name('button_border_size'); ?>" type="number" value="<?= $this->get_value('button_border_size'); ?>" placeholder=""> px
								<p class="description"><?= __('Will be applied to email input too.', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Connect Button Paddings', 'meta-locker'); ?></th>
							<td>
								<input name="<?= $this->get_name('button_padding_x'); ?>" type="number" value="<?= $this->get_value('button_padding_x'); ?>">
								<input name="<?= $this->get_name('button_padding_y'); ?>" type="number" value="<?= $this->get_value('button_padding_y'); ?>"> px
								<p class="description"><?= __('Horizontal padding.', 'meta-locker'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= __('Vertical padding.', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Cookie Duration', 'meta-locker'); ?></th>
							<td>
								<input name="<?= $this->get_name('cookie_duration'); ?>" type="number" value="<?= $this->get_value('cookie_duration'); ?>" placeholder=""> hours
								<p class="description"><?= __('The duration of the cookie set on authenticated users.', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Disable Auto Insert', 'meta-locker'); ?></th>
							<td>
								<label>
									<input type="checkbox" name="<?= $this->get_name('disable_auto_insert') ?>" value="1" <?php checked($this->get_value('disable_auto_insert'), 1) ?>>
									<span class="description"><?= __('By default, MetaLocker will lock content of every post automatically. If you want to disable that, check this box.', 'meta-locker') ?></span>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Auto-Insert Excerpt Length', 'meta-locker'); ?></th>
							<td>
								<input name="<?= $this->get_name('excerpt_length'); ?>" type="number" value="<?= $this->get_value('excerpt_length'); ?>" placeholder=""> characters
								<p class="description"><?= __('The length of the characters trimmed from the content in Auto-Insert mode.', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Default Callout Message', 'meta-locker'); ?></th>
							<td>
								<textarea name="<?= $this->get_name('message'); ?>" cols="60" rows="4"><?= $this->get_value('message'); ?></textarea>
								<p class="description"><?= __('The message asking users to connect their wallet.', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Checkbox Consent Text', 'meta-locker'); ?></th>
							<td>
								<textarea name="<?= $this->get_name('consent_text'); ?>" cols="60" rows="4"><?= $this->get_value('consent_text'); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Checkbox Consent Checked?', 'meta-locker'); ?></th>
							<td>
								<label>
									<input type="checkbox" name="<?= $this->get_name('checkbox_consent_state') ?>" value="1" <?php checked($this->get_value('checkbox_consent_state'), 1) ?>>
									<span class="description"><?= __('Yes, checked by default.', 'meta-locker') ?></span>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Minimum balance amount', 'meta-locker'); ?></th>
							<td>
								<input style="width:300px" type="number" name="<?= $this->get_name('minimum_balance') ?>" value="<?= $this->get_value('minimum_balance') ?>">
								<p class="description"><?= __('The minimum amount to check.', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Balance Error Message', 'meta-locker'); ?></th>
							<td>
								<textarea name="<?= $this->get_name('balance_message'); ?>" cols="60" rows="4"><?= $this->get_value('balance_message'); ?></textarea>
								<p class="description"><?= __('The insufficient balance error message.', 'meta-locker'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?= __('Privacy Policy Page URL', 'meta-locker'); ?></th>
							<td>
								<input style="width:300px" type="text" name="<?= $this->get_name('privacy_policy_url') ?>" value="<?= $this->get_value('privacy_policy_url') ?>">
								<p class="description"><?= __('The URL of the Privacy Policy page.', 'meta-locker'); ?></p>
							</td>
						</tr>
					</table>
				</div>
				<?php submit_button(); ?>
			</form>
	<?php
	}

	/**
	 * Enqueue assets
	 *
	 * @internal  Used as a callback.
	 */
	public function enqueueScripts($hook_name)
	{
		if ($hook_name !== $this->hook_name) {
			return;
		}

		wp_enqueue_style('pickr-classic', META_LOCKER_URI . 'assets/css/vendor/pickr-classic.min.css', array(), META_LOCKER_VER);

		wp_enqueue_script('settings-page', META_LOCKER_URI . 'assets/js/settings-page.min.js', array(), META_LOCKER_VER, true);
	}

	/**
	 * Get name
	 *
	 * @param  string $field  Key name.
	 *
	 * @return  string
	 */
	private function get_name($key)
	{
		return 'metaLockerSettings[' . $key . ']';
	}

	/**
	 * Get value
	 *
	 * @param  string $key  Key name.
	 *
	 * @return  mixed
	 */
	private function get_value($key)
	{
		return isset($this->settings[$key]) ? sanitize_text_field($this->settings[$key]) : '';
	}
}

// Initialize the Singleton.
MetaLocker_Settings_Page::init();
