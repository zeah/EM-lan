<?php 

defined('ABSPATH') or die('Blank Space');

final class Emlan_Shortcode {
	/*singleton*/
	private static $instance = null;

	private $desktop = EMLAN_PLUGIN_URL.'assets/css/emlan.css?v=1.0.7';
	private $mobile = EMLAN_PLUGIN_URL.'assets/css/emlan-mobile.css?v=1.0.0';
	private $added_js = false;

	public static function get_instance() {
		if (self::$instance === null) self::$instance = new self();

		return self::$instance;
	}

	private function __construct() {
		Emlan_Taxonomy::get_instance();

		add_shortcode('emlan', array($this, 'shortcode'));
		add_shortcode('emlån', array($this, 'shortcode'));

		add_shortcode('emlan-bilde', array($this, 'shortcode_bilde'));
		add_shortcode('emlan-bestill', array($this, 'shortcode_sok'));

		add_action('emlan_shortcode', array($this, 'emlan_shortcode'));

        add_filter('pre_get_posts', array($this, 'set_search'), 99);
		add_filter('add_google_fonts', array($this, 'add_google_fonts'), 99);
	}


	/*
		adds emlan custom post to search
	*/
	public function set_search($query) {
        if ($query->is_search) {
	        if (!$query->get('post_type')) $query->set('post_type', array('page', 'post', 'emlan'));
	        else $query->set('post_type', array_merge(array('emlan'), $query->get('post_type')));
		}
	}

	public function shortcode($atts, $content = null) {
		$this->add_css();

		$lan = null;
		if (isset($atts['lan'])) 		$lan = $atts['lan'];
		if (!isset($atts['name']))		return $this->do_loop(null, $lan);
		elseif (isset($atts['name']))	return $this->do_loop(explode(',', str_replace(' ', '', $atts['name'])), $lan);
	}

	private function do_loop($name = null, $lan = null) {
		$args = [
			'post_type' => 'emlan',
			'posts_per_page' => -1,
			'orderby' => array(
				'meta_value_num' => 'ASC',
				'title' => 'ASC'
			),
			'meta_key' => 'emlan_sort'
		];

		if ($lan) $args['tax_query'] = array(
						array(
							'taxonomy' => 'emlantype',
							'field' => 'slug',
							'terms' => esc_html($lan)
						)
					);

		if (is_array($name)) $args['post_name__in'] = $name; 
		
		$query = new WP_Query($args);

		// sorting posts as they are listed in [emkort name=""]
		if (is_array($name)) {
			$posts = [];

			foreach ($name as $n)
				foreach($query->posts as $p) 
					if ($n == $p->post_name) array_push($posts, $p);

			$query->posts = $posts; 
		}

		global $post;

		// container element
		$html = '<div class="emlanlist-container" style="opacity: 0">';

		if ($query->have_posts()) 
			while ($query->have_posts()) {
				$query->the_post();

				// to ignore "ignore" and/or "duplicate" taxonomies
				$terms = wp_get_post_terms($post->ID, 'emlantype');
				$ignore = false;
				foreach($terms as $term) {
					if ($term->slug == 'ignore') 		$ignore = true; // ignore all with ignore tag
					elseif ($term->slug == 'duplicate' 
						&& !$name && !$lan) 			$ignore = true; // ignore all with duplicate tag and name/kort att not used
				}

				if ($ignore) continue;

				// getting the meta
				$meta = get_post_meta($post->ID, 'emlan');

				if (isset($meta[0])) 	$meta = $meta[0];
				else 					continue; // if no meta, then no card

				// adding the meta
				$html .= $this->make_lan($meta);
			}

		wp_reset_postdata();

		$html .= '</div>';
		return $html;	
	}

	/*
		printing out one lån
	*/
	private function make_lan($meta) {
		global $post;
		// wp_die(print_r($post, true));

		// $html = print_r($meta, true);

		$html = '<div class="emlan-container">';

		// first row
		$html .= '<div class="emlan-row emlan-toprow">';

		$lesmer = isset($meta['lesmer']) ? $meta['lesmer'] : '';

		// thumbnail
		$thumbnail = get_the_post_thumbnail_url($post, 'full');
		if ($thumbnail) $html .= '<div class="emlan-thumbnail">'.($lesmer ? '<a href="'.esc_url($lesmer).'">' : '').'<img class="emlan-thumbnail-image" src="'.esc_url($thumbnail).'">'.($lesmer ? '</a>' : '').'</div>';

		// lånebeløp
		$belop = isset($meta['belop']) ? $meta['belop'] : '';
		if ($belop) $html .= '<div class="emlan-belop">'.esc_html($belop).'</div>';
		
		// nedbetalingstid
		$nedbetaling = isset($meta['nedbetaling']) ? $meta['nedbetaling'] : '';
		if ($nedbetaling) $html .= '<div class="emlan-nedbetaling">'.esc_html($nedbetaling).'</div>';
		
		// aldersgrense
		$alder = isset($meta['alder']) ? $meta['alder'] : '';
		if ($alder) $html .= '<div class="emlan-alder">'.esc_html($alder).'</div>';
		
		// effektiv rente
		$effrente = isset($meta['effrente']) ? $meta['effrente'] : '';
		if ($effrente) $html .= '<div class="emlan-effrente">'.esc_html($effrente).'</div>';
		
		// få tilbud
		$fatilbud = isset($meta['fatilbud']) ? $meta['fatilbud'] : '';
		if ($fatilbud) $html .= '<div class="emlan-fatilbud"><a target="_blank" rel="noopener" class="emlan-lenke emlan-lenke-fatilbud" href="'.esc_url($fatilbud).'">Få Tilbud Nå</a></div>';
		
		$html .= '</div>';

		$html .= '<div class="emlan-row emlan-middlerow">';

		// info 1
		$info1 = isset($meta['info1']) ? $meta['info1'] : '';
		if ($info1) $html .= '<div class="emlan-info1"><i class="material-icons md-18">grade</i> '.esc_html($info1).'</div>';
		
		// info 2
		$info2 = isset($meta['info2']) ? $meta['info2'] : '';
		if ($info2) $html .= '<div class="emlan-info2"><i class="material-icons md-18">grade</i> '.esc_html($info2).'</div>';
		
		// info 3
		$info3 = isset($meta['info3']) ? $meta['info3'] : '';
		if ($info3) $html .= '<div class="emlan-info3"><i class="material-icons md-18">grade</i> '.esc_html($info3).'</div>';
		
		$html .= '</div>';

		$html .= '<div class="emlan-row emlan-bottomrow">';

		// eks eff rente
		$ekseffrente = isset($meta['ekseffrente']) ? $meta['ekseffrente'] : '';
		if ($ekseffrente) $html .= '<div class="emlan-ekseffrente">'.esc_html($ekseffrente).'</div>';
		
		// les mer
		// $lesmer = isset($meta['lesmer']) ? $meta['lesmer'] : '';
		// if ($lesmer) $html .= '<div class="emlan-lesmer"><a class="emlan-lenke-lesmer" href="'.esc_url($lesmer).'">Les mer om '.$post->post_title.'</a></div>';
		if ($lesmer) $html .= '<div class="emlan-lesmer"><a class="emlan-lenke-lesmer" href="'.esc_url($lesmer).'">Les mer</a></div>';
		
		$html .= '</div>';


		$html .= '</div>';

		return $html;
	}

	/*
		shortcode for adding picture of lån
	*/
	public function shortcode_bilde($atts, $content = null) {
		if (! isset($atts['name'])) return;

		$post = get_posts([
							'name'        => $atts['name'],
							'post_type'   => 'emlan',
							'post_status' => 'publish',
							'numberposts' => 1
						]);

		if (! isset($post[0])) return;

		$thumbnail = get_the_post_thumbnail_url($post[0], 'full');
		if ($thumbnail) return '<div class="emlan-bilde"><img class="emlan-bilde-image" src="'.esc_url($thumbnail).'"></div>';
					
	}

	/*
		shortcode for adding button for "Få Tilbud Nå"
	*/
	public function shortcode_sok($atts, $content = null) {
		if (! isset($atts['name'])) return;

		$this->add_css();
		return '<div class="emlan-fatilbud-container"><a target="_blank" rel="noopener" class="emlan-lenke emlan-lenke-fatilbud" href="'.esc_url($this->get_meta($atts['name'], 'fatilbud')).'">Få Tilbud Nå</a></div>'; 
	}

	/*
		helper function for adding css
		adds javascript to footer that adds css files to header
	*/
	private function add_css() {
		if (! $this->added_js) {
			add_action('wp_footer', array($this, 'footer'));
			$this->add_js = true;
		}
	}

	/*
		adding js to footer which adds css to header
	*/
	public function footer() {
		echo '<script defer>
				(function() {
					var o = document.createElement("link");
					o.setAttribute("rel", "stylesheet");
					o.setAttribute("href", "'.esc_html($this->desktop).'");
					o.setAttribute("media", "(min-width: 1025px)");
					document.head.appendChild(o);

					var m = document.createElement("link");
					m.setAttribute("rel", "stylesheet");
					m.setAttribute("href", "'.esc_html($this->mobile).'");
					m.setAttribute("media", "(max-width: 1024px)");
					document.head.appendChild(m);

				})();
			  </script>';
	}

	/*
		theme search hook
	*/
	public function emlan_shortcode($post_id) {
		add_action('wp_footer', array($this, 'footer'));

		$meta = get_post_meta($post_id, 'emlan');

		// echoing one lån
		if (isset($meta[0])) echo $this->make_lan($meta[0]); 
	}

	/*
		hooking into emtheme for adding google fonts
	*/
	public function add_google_fonts($value) {

		return $value;
	}

	/*
		helper function for getting meta data
	*/
	private function get_meta($slug, $meta_name, $ignore_check = null) {

		// getting post
		$post = get_posts([
			'name'        => $slug,
			'post_type'   => 'emlan',
			'post_status' => 'publish',
			'numberposts' => 1
		]);

		// if no post found
		if (! $post) return false;

		// fix return value
		$post = $post[0];
		
		// if to be ignored
		if ($ignore_check) {
			$terms = wp_get_post_terms($post->ID, 'emlantype');

			foreach($terms as $t) 
				if ($t->slug == $ignore_check) return false;
		}

		// getting meta
		$meta = get_post_meta($post->ID, 'emlan');
		if (! isset($meta[0][$meta_name])) return false;

		// returning meta
		return $meta[0][$meta_name];
	}
}