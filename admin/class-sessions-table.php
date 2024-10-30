<?php

/**
 * MetaLocker_Sessions_Table
 *
 * List table showing all connected sessions
 */
class MetaLocker_Sessions_Table extends WP_List_Table
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $status, $page;

		parent::__construct(
			array(
				'singular' => 'session',
				'plural'   => 'sessions',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Default column output
	 *
	 * @param array $item A singular item (one full row's worth of data).
	 * @param array $column_name The name/slug of the column to be processed.
	 * @return string Text or HTML to be placed inside the column <td>.
	 */
	public function column_default($item, $column_name)
	{
		return $item[$column_name];
	}

	/**
	 * Show visited timestamp
	 *
	 * @param array $item Current item.
	 * @return string
	 */
	public function column_visited_time($item)
	{
		if (empty($item['visited_time'])) {
			return __('Unknown', 'meta-locker');
		}

		$datetime = new DateTime($item['visited_time']);

		return $datetime->format('g:i A \o\n F j, Y');
	}

	/**
	 * Show visitor IP
	 *
	 * @param array $item Current item.
	 * @return string
	 */
	public function column_ip($item)
	{
		if (empty($item['ip'])) {
			return __('Unknown', 'meta-locker');
		}

		return $item['ip'];
	}

	/**
	 * Show user agent
	 *
	 * @param array $item Current item.
	 * @return string
	 */
	public function column_agent($item)
	{
		if (empty($item['agent'])) {
			return __('Unknown', 'meta-locker');
		}

		return $item['agent'];
	}

	/**
	 * ID checkboxes
	 *
	 * @param array $item Current item.
	 * @return string
	 */
	public function column_cb($item)
	{
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/
			$this->_args['singular'],
			/*$2%s*/
			$item['id']
		);
	}

	/**
	 * REQUIRED! This method dictates the table's columns and titles. This should
	 * return an array where the key is the column slug (and class) and the value
	 * is the column's title text. If you need a checkbox for bulk actions, refer
	 * to the $columns array below.
	 *
	 * The 'cb' column is treated differently than the rest. If including a checkbox
	 * column in your table you must create a column_cb() method. If you don't need
	 * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
	 */
	public function get_columns()
	{
		$columns = array(
			'cb'             => '<input type="checkbox" />',
			'email'          => __('Email', 'meta-locker'),
			'wallet_address' => __('Wallet Address', 'meta-locker'),
			'wallet_type'    => __('Wallet Type', 'meta-locker'),
			'balance'        => __('Balance', 'meta-locker'),
			'ip'             => __('IP', 'meta-locker'),
			'link'           => __('Visited URL', 'meta-locker'),
			'visited_time'   => __('Datetime', 'meta-locker'),
			'agent'          => __('User Agent', 'meta-locker'),
		);

		return $columns;
	}

	/** ************************************************************************
	 * Optional. If you want one or more columns to be sortable (ASC/DESC toggle),
	 * you will need to register it here. This should return an array where the
	 * key is the column that needs to be sortable, and the value is db column to
	 * sort by. Often, the key and value will be the same, but this is not always
	 * the case (as the value is a column name from the database, not the list table).
	 *
	 * This method merely defines which columns should be sortable and makes them
	 * clickable - it does not handle the actual sorting. You still need to detect
	 * the ORDERBY and ORDER querystring variables within prepare_items() and sort
	 * your data accordingly (usually by modifying your query).
	 *
	 * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
	 **************************************************************************/
	public function get_sortable_columns()
	{
		$sortable_columns = array(
			'email'   => array('email', false),
			'balance' => array('balance', false),
		);

		return $sortable_columns;
	}

	/** ************************************************************************
	 * Optional. If you need to include bulk actions in your list table, this is
	 * the place to define them. Bulk actions are an associative array in the format
	 * 'slug'=>'Visible Title'
	 *
	 * If this method returns an empty value, no bulk action will be rendered. If
	 * you specify any bulk actions, the bulk actions box will be rendered with
	 * the table automatically on display().
	 *
	 * Also note that list tables are not automatically wrapped in <form> elements,
	 * so you will need to create those manually in order for bulk actions to function.
	 *
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
	 **************************************************************************/
	public function get_bulk_actions()
	{
		$actions = array(
			'delete' => 'Delete',
		);

		return $actions;
	}

	/** ************************************************************************
	 * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
	 * For this example package, we will handle it in the class to keep things
	 * clean and organized.
	 *
	 * @see $this->prepare_items()
	 **************************************************************************/
	public function process_bulk_action()
	{
		global $wpdb;

		// Detect when a bulk action is being triggered...
		if ('delete' === $this->current_action() && !empty($_GET['session'])) {
			foreach ($_GET['session'] as $session_id) {
				$id = intval($session_id);
				$wpdb->query("DELETE FROM metalocker_sessions WHERE id={$id};");
			}
		}
	}

	/** ************************************************************************
	 * REQUIRED! This is where you prepare your data for display. This method will
	 * usually be used to query the database, sort and filter the data, and generally
	 * get it ready to be displayed. At a minimum, we should set $this->items and
	 * $this->set_pagination_args(), although the following properties and methods
	 * are frequently interacted with here...
	 *
	 * @global WPDB $wpdb
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 **************************************************************************/
	public function prepare_items()
	{
		global $wpdb;

		/**
		 * First, lets decide how many records per page to show
		 */
		$per_page = 10;

		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array($columns, $hidden, $sortable);

		/**
		 * Optional. You can handle your bulk actions however you see fit. In this
		 * case, we'll handle them within our package just to keep things clean.
		 */
		$this->process_bulk_action();

		$data = (array) $wpdb->get_results('SELECT * FROM metalocker_sessions ORDER BY id DESC;', ARRAY_A);

		/**
		 * REQUIRED for pagination. Let's figure out what page the user is currently
		 * looking at. We'll need this later, so you should always include it in
		 * your own package classes.
		 */
		$current_page = $this->get_pagenum();

		/**
		 * REQUIRED for pagination. Let's check how many items are in our data array.
		 * In real-world use, this would be the total number of items in your database,
		 * without filtering. We'll need this later, so you should always include it
		 * in your own package classes.
		 */
		$total_items = count($data);

		/**
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to
		 */
		$data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = $data;

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil($total_items / $per_page),
			)
		);
	}

	/**
	 * @global string $comment_status
	 * @global string $comment_type
	 *
	 * @param string $which
	 */
	protected function extra_tablenav($which)
	{
		global $wpdb;

		$last_visit   = $wpdb->get_var('SELECT visited_time FROM metalocker_sessions ORDER BY id DESC LIMIT 1;');
		$first_visit  = $wpdb->get_var('SELECT visited_time FROM metalocker_sessions ORDER BY id ASC LIMIT 1;');
		$start_date   = new DateTime($first_visit);
		$end_date     = new DateTime($last_visit);
		$end_date     = $end_date->modify('+1 day');
		$interval     = DateInterval::createFromDateString('1 day');
		$date_periods = new DatePeriod($start_date, $interval, $end_date);
		$options      = '';

		foreach ($date_periods as $period) {
			$datetime = $period->format('Y-m-d');
			$options .= '<option value="' . $datetime . ' 00:00:00">' . $datetime . '</option>';
		}

		if ('top' === $which && $this->has_items()) {
?>
			<div class="alignright actions">
				<select name="metalocker_csv_startdate" style="margin-left:10px">
					<option value=""><?php echo __('Start Date', 'meta-locker'); ?></option>
					<?php echo $options; ?>
				</select>
				<select name="metalocker_csv_enddate">
					<option value=""><?php echo __('End Date', 'meta-locker'); ?></option>
					<?php echo $options; ?>
				</select>
				<input type="submit" name="metalocker_export_csv" class="button button-primary" value="<?= __('Export CSV', 'meta-locker') ?>">
			</div>
	<?php
		}
	}
}

/** *************************** RENDER THE LIST ********************************
 * ******************************************************************************
 * This function renders the admin page and the example list table. Although it's
 * possible to call prepare_items() and display() from the constructor, there
 * are often times where you may need to include logic here between those steps,
 * so we've instead called those methods explicitly. It keeps things flexible, and
 * it's the way the list tables are used in the WordPress core.
 */
function metalocker_render_sessions_list()
{
	// Create an instance of our package class...
	$list_table = new MetaLocker_Sessions_Table();

	// Fetch, prepare, sort, and filter our data...
	$list_table->prepare_items();

	?>
	<div class="wrap">
		<h2><?php esc_html_e('All Connected Wallets', 'meta-locker'); ?></h2>
		<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
		<form id="sessions-filter" method="get">
			<!-- For plugins, we also need to ensure that the form posts back to our current page -->
			<input type="hidden" name="page" value="<?php echo sanitize_title($_REQUEST['page']); ?>" />
			<!-- Now we can render the completed list table -->
			<?php $list_table->display(); ?>
		</form>

	</div>
<?php
}

/** ************************ REGISTER THE LIST PAGE ****************************
 * ******************************************************************************
 * Now we just need to define an admin page. For this example, we'll add a top-level
 * menu item to the bottom of the admin menus.
 */
function metalocker_add_sessions_list_menu()
{
	add_submenu_page('metalocker-tos', __('Connected Sessions', 'meta-locker'), __('Connected Sessions', 'meta-locker'), 'manage_options', 'metalocker-sessions', 'metalocker_render_sessions_list');
}
// add_action('admin_menu', 'metalocker_add_sessions_list_menu');
