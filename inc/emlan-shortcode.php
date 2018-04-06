<?php 

defined('ABSPATH') or die('Blank Space');

final class Emlan_Shortcode {
	/*singleton*/
	private static $instance = null;

	private $desktop = EMLAN_PLUGIN_URL.'assets/css/emlan.css';
	private $mobile = EMLAN_PLUGIN_URL.'assets/css/emlan-mobile.css';
	private $added_js = false;

	public static function get_instance() {
		if (self::$instance === null) self::$instance = new self();

		return self::$instance;
	}

	private function __construct() {
		Emlan_Taxonomy::get_instance();

		add_shortcode('emlan', array($this, 'shortcode'));
		add_shortcode('emlån', array($this, 'shortcode'));
	}

	public function shortcode($atts, $content = null) {
		$this->add_css();

		$kort = null;
		if (isset($atts['kort'])) 		$kort = $atts['kort'];
		if (!isset($atts['name']))		return $this->do_loop(null, $kort);
		elseif (isset($atts['name']))	return $this->do_loop(explode(',', str_replace(' ', '', $atts['name'])), $kort);
	}

	private function do_loop($name = null, $kort = null) {
		$args = [
			'post_type' => 'emlan',
			'posts_per_page' => -1,
			'orderby' => array(
				'meta_value_num' => 'ASC',
				'title' => 'ASC'
			),
			'meta_key' => 'emlan_sort'
		];

		if ($kort)
			$args['tax_query'] = array(
					array(
						'taxonomy' => 'korttype',
						'field' => 'slug',
						'terms' => esc_html($kort)
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
		$html = '<div class="emlanlist-container">';
		// $html = '<div class="emlanliste-container" style="opacity: 0">';

		if ($query->have_posts()) 
			while ($query->have_posts()) {
				$query->the_post();


				// to ignore "ignore" and/or "duplicate" taxonomies
				$terms = wp_get_post_terms($post->ID, 'emlantype');
				$ignore = false;
				foreach($terms as $term) {
					if ($term->slug == 'ignore') 		$ignore = true; // ignore all with ignore tag
					elseif ($term->slug == 'duplicate' 
						&& !$name && !$kort) 			$ignore = true; // ignore all with duplicate tag and name/kort att not used
				
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

	private function make_lan($meta) {
		global $post;

		$html = print_r($meta, true);

		$html .= '<div class="emlan-container">';

		// first row
		$html .= '<div class="emlan-row emlan-toprow">';

		// thumbnail
		$thumbnail = get_the_post_thumbnail_url($post, 'full');
		if ($thumbnail) $html .= '<div class="emlan-thumbnail"><img src="'.$thumbnail.'"></div>';

		// lånebeløp
		$belop = isset($meta['belop']) ? $meta['belop'] : '';
		if ($belop) $html .= '<div class="emlan-belop">'.$belop.'</div>';
		
		// nedbetalingstid
		$nedbetaling = isset($meta['nedbetaling']) ? $meta['nedbetaling'] : '';
		if ($nedbetaling) $html .= '<div class="emlan-nedbetaling">'.$nedbetaling.'</div>';
		
		// aldersgrense
		$alder = isset($meta['alder']) ? $meta['alder'] : '';
		if ($alder) $html .= '<div class="emlan-alder">'.$alder.'</div>';
		
		// effektiv rente
		$effrente = isset($meta['effrente']) ? $meta['effrente'] : '';
		if ($effrente) $html .= '<div class="emlan-effrente">'.$effrente.'</div>';
		
		// få tilbud
		$fatilbud = isset($meta['fatilbud']) ? $meta['fatilbud'] : '';
		if ($fatilbud) $html .= '<div class="emlan-fatilbud"><a class="emlan-lenke emlan-lenke-fatilbud" href="'.$fatilbud.'">Få Tilbud Nå</a></div>';
		
		$html .= '</div>';

		$html .= '<div class="emlan-row emlan-middlerow">';

		// info 1
		$info1 = isset($meta['info1']) ? $meta['info1'] : '';
		if ($info1) $html .= '<div class="emlan-info1">'.$info1.'</div>';
		
		// info 2
		$info2 = isset($meta['info2']) ? $meta['info2'] : '';
		if ($info2) $html .= '<div class="emlan-info2">'.$info2.'</div>';
		
		// info 3
		$info3 = isset($meta['info3']) ? $meta['info3'] : '';
		if ($info3) $html .= '<div class="emlan-info3">'.$info3.'</div>';
		
		$html .= '</div>';

		$html .= '<div class="emlan-row emlan-bottomrow">';

		// eks eff rente
		$ekseffrente = isset($meta['ekseffrente']) ? $meta['ekseffrente'] : '';
		if ($ekseffrente) $html .= '<div class="emlan-ekseffrente">'.$ekseffrente.'</div>';
		
		// les mer
		$lesmer = isset($meta['lesmer']) ? $meta['lesmer'] : '';
		if ($lesmer) $html .= '<div class="emlan-lesmer"><a class="emlan-lenke emlan-lenke-lesmer" href="'.$lesmer.'">Les Mer</a></div>';
		
		$html .= '</div>';


		$html .= '</div>';

		return $html;
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
}