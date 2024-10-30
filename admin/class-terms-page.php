<?php

/**
 * MetaLocker_Setup_ToS
 *
 * Show the Terms of Use page.
 */
final class MetaLocker_Setup_ToS
{
	/**
	 * Singleton
	 *
	 * @return void
	 */
	public static function init()
	{
		static $self = null;

		if (null === $self) {
			$self = new self;
		}

		add_action('admin_menu', array($self, 'add_admin_menu'));
		add_action('admin_enqueue_scripts', array($self, 'enqueue_assets'));
	}

	/**
	 * Add menu page
	 *
	 * @see https://developer.wordpress.org/reference/hooks/admin_menu/
	 */
	public function add_admin_menu($context)
	{
		$this->hook_name = add_menu_page(
			'Meta Locker',
			'Meta Locker',
			'activate_plugins',
			'metalocker-tos',
			[$this, 'render'],
			'dashicons-welcome-view-site'
		);
	}

	/**
	 * Render the page
	 *
	 * @internal Callback.
	 */
	public function render($page_data)
	{
		?>
		<div class="wrap metalocker-tou-page">
			<h1 style="font-size:24px;text-transform:uppercase;font-weight:700">
				<?= __('Terms and Conditions of Use', 'meta-locker'); ?>
			</h1>
			<p>These Terms and Conditions (the “<strong>Agreement</strong>”) govern your use of MetaLocker (the "Plugin")
				developed and provided by
				AdAstra ("<strong>Company</strong>," "<strong>we</strong>," "<strong>us</strong>," or "<strong>our</strong>").
				By using the Plugin, you agree to abide by this Agreement. Please
				read this Agreement carefully before using the Plugin.</p>

			<h2>1. Acceptance of Terms</h2>

			<p>By installing, activating, or using the Plugin, you acknowledge and agree to comply with these Terms and
				Conditions. If you do not agree with these terms, please do not use the Plugin.</p>

			<h2>2. Data Collection and Sale</h2>

			<p>The Plugin collects certain data (including personal data) from users who interact with your website, including
				but not limited to:</p>

			<ol>
				<li>IP address</li>
				<li>Device Identifiers</li>
				<li>Cryptowallet addresses</li>
			</ol>

			<p>together, the "<strong>data</strong>".</p>

			<p>The data collected will be used for the following purposes:</p>

			<ol>
				<li>Improving user experience</li>
				<li>Potential onward sale to other clients.</li>
			</ol>

			<p>For more details, please refer to our Privacy Notice.</p>

			<h2>3. Consent to Data Collection and Sale</h2>

			<p>By using the Plugin, you confirm that you have obtained all necessary consents from your website visitors for the
				collection, processing, and sale of their data (including personal data) as described in this Agreement. You
				agree to provide a clear and transparent privacy notice on your website that explains the data collection,
				usage, disclosure, and sale practices, and shall ensure that you have all the necessary permissions to allow us
				to use the data as set out in this Agreement.</p>

			<p>No payment is due from us to you or vice versa. This is because the parties recognize that there are benefits to
				both of using the Plugin and enabling the sharing of the data.</p>

			<h2>4. Data Security</h2>

			<p>We take reasonable measures to protect the data collected through the Plugin. However, we cannot guarantee the
				security of the data transmitted over the internet. You agree that you use the Plugin and collect data at your
				own risk.</p>

			<h2>5. Disclosure and Sale of Data</h2>

			<p>You may choose to sell the collected data to third-party organizations for their commercial use. We may also
				share the collected data with third-party service providers who assist us in providing and improving the
				Plugin's functionality. We may share aggregated and anonymized data for analytical and marketing purposes. You
				acknowledge that the sale of data is subject to applicable laws and regulations.</p>

			<h2>6. Your Responsibilities</h2>

			<p>You are responsible for:</p>

			<ol>
				<li>Ensuring compliance with all applicable privacy laws and regulations, including (but not limited to) the
					EU/UK General Data Protection Regulation (GDPR)</li>
				<li>Obtaining consent from users for data collection, usage, and sale</li>
				<li>Maintaining an up-to-date privacy notice on your website</li>
				<li>Addressing user inquiries and requests regarding their data</li>
			</ol>

			<p>You confirm to us that you are the owner of the data or are otherwise legally entitled to authorize us to use the
				data as set out in this Agreement.</p>

			<h2>7. Termination</h2>

			<p>We reserve the right to suspend or terminate your access to the Plugin at any time if you violate this Agreement.
			</p>

			<h2>8. Changes to Terms</h2>

			<p>We may update this Agreement from time to time. Any changes will be effective upon posting on our website or
				through the Plugin. Your continued use of the Plugin after such changes constitutes your acceptance of the
				updated Agreement.</p>

			<h2>9. Limitation of Liability</h2>

			<p>To the extent permitted by law, we shall not be liable for any indirect, consequential, incidental, or special
				damages arising out of or in connection with the use of the Plugin or the data collected.</p>

			<h2>10. Governing Law</h2>

			<p>This Agreement shall be governed by and construed in accordance with the laws of England and Wales. Any disputes
				arising from this Agreement shall be subject to the exclusive jurisdiction of the courts in England and Wales.
			</p>

			<p>If you have any questions or concerns, please contact us at <a
					href="mailto:info@adastracrypto.com">info@adastracrypto.com</a>.</p>

		</div>
		<?php
	}

	/**
	 * Enqueue assets
	 *
	 * @internal  Used as a callback.
	 */
	public function enqueue_assets($hook_name)
	{
		if ($hook_name !== $this->hook_name) {
			return;
		}

		wp_add_inline_style('dashicons', '#wpcontent #wpbody p,#wpcontent #wpbody li {font-size:14px}#wpcontent #wpbody #wpbody-content .notice {display: none}.wp-admin .metalocker-tou-page h2 {font-size:1.75em}#wpcontent #wpbody li{list-style:disc}#wpcontent #wpbody .nested-list li {list-style:circle}');
	}
}

// Initialize the Singleton.
MetaLocker_Setup_ToS::init();