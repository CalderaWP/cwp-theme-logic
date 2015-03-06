<?php
/**
 * Class for single post data.
 *
 * @package   @cwp-theme
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 Josh Pollock
 */

class CWP_Theme_Data {
	/**
	 * @var object|\Pods
	 */
	public $pod;

	/**
	 * @var object|\WP_Post
	 */
	public $post;

	/**
	 * Attributes for use in header.
	 *
	 * @var array
	 */
	public $header_atts;


	/**
	 * CWP Logo HTML
	 *
	 * @var string
	 */
	public $logo;

	/**
	 * Whether we are using Pods data or not.
	 *
	 * @var bool
	 */
	protected $use_pods;

	/**
	 * Name of default menu
	 */
	protected $menu_name = 'primary';

	/**
	 * @var string
	 */
	public $header;

	public $header_size = '250px';

	/**
	 * Constructor for class
	 *
	 * @param WP_Post|object|bool $post Post object or false if on archive page
	 * @param bool|int  $header_id Optional. ID of logo for page. If false, it will set current post featured image, or CWP logo.
	 * @param bool||Pods $use_pods Optional. To use Pods or not. If is true, a Pods object will be created. If is a Pods object, that will be used.
	 */
	public function __construct( $post, $header_id = false, $use_pods = false ) {
		$this->post = $post;
		$this->use_pods = $use_pods;

		$this->logo = wp_get_attachment_image( cwp_theme_cwp_logo_id( true ) );

		if ( true === $use_pods ) {
			$this->pod = $this->pod();
			$this->use_pods = true;
		}elseif( is_a( $use_pods, 'Pods' ) ) {
			$this->pod = $use_pods;
			$this->use_pods = true;
		}else{
			$this->use_pods = false;
		}

		if ( $header_id ) {
			$this->header = wp_get_attachment_image( $header_id, 'large' );
		}else{
			$this->header = $this->get_header_bg();
		}

		$this->header_atts = $this->header_atts();




	}

	/**
	 * Header attributes
	 *
	 * @return array
	 */
	protected function header_atts() {

		$atts = array(
			'tagline' => '',
			'header_bg' => $this->header,
			'title' => $this->post->post_title,
			'header_size' => $this->header_size
		);

		if ( is_front_page() ) {
			$atts[ 'tagline' ] = get_bloginfo( 'description' );
		}

		$atts[ 'logo' ] = $this->logo;


		return $atts;

	}


	/**
	 * Sets HTML for the header BG img as the value for $this->header if it wasn't set with class params.
	 *
	 * @return string
	 */
	protected function get_header_bg() {
		$header = (int) get_post_thumbnail_id( $this->post->ID );
		if ( 0 == $header || 9 == $header ) {
			$header = cwp_theme_cwp_logo_id();

		}

		if ( ! cwp_theme_is_plugin_page() ) {
			$this->header_size = "250px";
		}

		$header = wp_get_attachment_image_src( $header, 'large' );


		return $header[0];
	}

	/**
	 * Data for testimonials
	 *
	 * @todo figure out how to handle this. Should be tweet IDs.
	 *
	 * @return array
	 */
	protected function testimonals_data() {
		return array();
	}

	/**
	 * Create HTML markup for testimonials section.
	 *
	 * @return string
	 */
	public function testimonials_section() {
  		if ( empty( $this->testimonals_data() ) ) {
			return;
		}

		wp_enqueue_script( 'bootstrap' );

		$i = 0;
		$out[] = '<!--Testimonials Section--><section class="testimonial-bg"><div id="testimonial" class="carousel slide" data-ride="carousel"><div class="carousel-inner" role="listbox">';

		foreach( $this->testimonals_data() as $testimonial ) {
			$active = false;
			if ( $i == 0 ) {
				$active = true;
			}

			$out[] = $this->testimonial( $testimonial, $active );
			$i++;

		}

		$out[] = '</div></div></section>';
		$out[] = "<style>jQuery( '#testimonial' ).carousel({interval: 6000});</style>";

		return implode( '', $out );

	}

	/**
	 * Create HTML Markup for a single testimonial.
	 *
	 * @param string $url The tweet URL
	 *
	 * @return string
	 */
	protected function testimonial( $url, $active = false ) {

		$parsed = parse_url( $url );
		if ( is_ssl() && $parsed[ 'scheme' ] !== 'https' ) {
			$parsed[ 'scheme' ] = 'https://';
			$url = implode( $parsed );
		}

		$class = '';
		if ( $active ) {
			$class = 'active';
		}

		return sprintf(
			'<div class="item %1s">
      			%2s
    		</div>',
			$class,
			wp_oembed_get( esc_url( $url ) )
		);


	}

	/**
	 * Get a new pod object for this post.
	 *
	 * @return object|\Pods
	 */
	public function pod( ) {


		$this->pod = pods( $this->post->post_type, $this->post->ID );

		return $this->pod;

	}

	/**
	 * Output a menu
	 *
	 * @param bool|string $menu_name Optional. The menu name to use. Defaults to $this->menu_name
	 *
	 * @return string
	 */
	public function menu( $menu_name = false ) {
		if ( ! $menu_name ) {
			$menu_name = $this->menu_name;
		}

		$menu_items = wp_get_nav_menu_items($menu_name);

		$menu_list = '<ul id="menu-' . $menu_name . '" class="navi">';
		
		foreach ( (array) $menu_items as $key => $item ) {
			if ( $item->attr_title ) {
				$title = $item->attr_title;
			}else{
				$title = $item->title;
			}
			$url = $item->url;
			$menu_list .= '<li><a href="' . esc_url( $url ) . '" title="' . esc_attr( $item->post_excerpt ) .'">' . $title . '</a></li>';
		}
		$menu_list .= '</ul>';

		return $menu_list;


	}

	/**
	 * Output contact section
	 *
	 * @return string
	 */
	public function contact_section() {
		return sprintf(
			'<!--Contact Section--><section id="contact">
				<div class="container contact-info">%1s</div>
				<div class="container" id="contact-inner">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 contact">
						<h1>Have A Question?</h1>
						<h2>If you have a pre-sale question, or are a registered user who needs support, or just want to say hi, get in touch.</h2>
					</div>
				</div>
				<div class="container contact-form">
					%2s
				</div>
			</section>',
			CWP_Theme_Social::cwp_social_links(),
			Caldera_Forms::render_form( 'CF54d702af07cef' )
		);

	}

	/**
	 * Output post content
	 *
	 * @return string
	 */
	public function post_content() {

		// content
		$content = apply_filters( 'the_content', $this->post->post_content );

		$out[] = '<section class="container single-post"><div class="post-content col-lg-12 col-sm-12">';
		$out[] = $content;//wptexturize( wpautop( do_shortcode(  ) ) );
		$out[] = '</div></section>';

		return implode( '', $out );
	}


}
