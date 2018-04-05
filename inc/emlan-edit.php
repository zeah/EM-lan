<?php 

defined('ABSPATH') or die('Blank Space');

final class Emlan_Edit {
	/*singleton*/
	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) self::$instance = new self();

		return self::$instance;
	}

	private function __construct() {
		/* adding javascript to emlan list screen */
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_script') );

		add_action('manage_emlan_posts_columns', array($this, 'column_head'));
		add_filter('manage_emlan_posts_custom_column', array($this, 'custom_column'));
		add_filter('manage_edit-emlan_sortable_columns', array($this, 'sort_column'));
		
		/* metabox, javascript */
		add_action('add_meta_boxes_emlan', array($this, 'create_meta'));

		/* hook for page saving/updating */
		add_action('save_post', array($this, 'save'));
	}

	public function enqueue_script() {
		wp_enqueue_style('emlan_admin_style', EMLAN_PLUGIN_URL . '/assets/css/emlan_admin.css', array(), false);
		wp_enqueue_script('emlan_meta', EMLAN_PLUGIN_URL . '/assets/js/emlan-admin.js', array(), false, true);
	}

	public function create_meta() {
		add_meta_box(
			'emlan_meta', // name
			'Lån Info', // title 
			array($this,'create_meta_box'), // callback
			'emlan' // page
		);
	}

	public function create_meta_box($post) {
		wp_nonce_field('em'.basename(__FILE__), 'em_nonce');

		$meta = get_post_meta($post->ID, 'emlan');
		$json = ['meta' => isset($meta[0]) ? $this->sanitize($meta[0]) : ''];

		wp_localize_script('emlan_meta', 'emlan', json_decode(json_encode($json), true));
		echo '<div class="emlan-container"></div>';
	}


	public function save($post_id) {
		if (!get_post_type($post_id) == 'emkort') return;

		// is on admin screen
		if (!is_admin()) return;

		// user is logged in and has permission
		if (!current_user_can('edit_posts')) return;

		// nonce is sent
		if (!isset($_POST['em_nonce'])) return;

		// nonce is checked
		if (!wp_verify_nonce($_POST['em_nonce'], 'em'.basename(__FILE__))) return;

		if (isset($_POST['emlan'])) update_post_meta($post_id, 'emlan', $this->sanitize($_POST['emlan']));

	}

	/*
		recursive sanitizer
		array or text
	*/
	private function sanitize($data) {
		if (!is_array($data))
			return sanitize_text_field($data);

		$d = [];
		foreach($data as $key => $value)
			$d[$key] = $this->sanitize($value);

		return $d;
	}

	public function column_head($defaults) {
		// $defaults['emkort_sort'] = 'Sorting Order';
		// $defaults['make_list'] = 'Make List';
		// return $defaults;
	}

	public function custom_column($column_name) {
		// global $post;

		// if ($column_name == 'emkort_sort') {
		// 	$meta = get_post_meta($post->ID, 'emkort_sort');

		// 	if (isset($meta[0]))
		// 		echo $meta[0];
		// }

		// if ($column_name == 'make_list')
		// 	echo '<button type="button" class="emkort-button button" data="'.$post->post_name.'">Add</button>';
	}

	public function sort_column($columns) {
		// $columns['emkort_sort'] = 'emkort_sort';
		// return $columns;
	}
}