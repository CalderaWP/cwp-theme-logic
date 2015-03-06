<?php


class CWP_Theme_Plugin_Page extends CWP_Theme_Data {


	/**
	 * Array of prices for this product
	 *
	 * @var array
	 */
	public $pricing;

	/**
	 * Is this a CF Add-on?
	 *
	 * @var bool
	 */
	public $cf;

	/**
	 * Is plugin coming soon?
	 *
	 * @var bool
	 */
	public $coming_soon = false;

	/**
	 * Is free plugin?
	 *
	 * @var bool
	 */
	public $free;

	/**

	/**
	 * Constructor for class
	 *
	 * @param object|WP_Post $post Post object
	 */
	public function __construct( $post) {
		$this->post = $post;
		$this->pod = $this->pod();

		if ( 'free_plugin' == $this->post->post_type ) {
			$this->free = true;

		}else{
			$this->free = false;
			$this->pricing = $this->pricing();
		}

		$this->logo = wp_get_attachment_image( cwp_theme_cwp_logo_id( true ) );
		$this->header_atts = $this->header_atts();



		$this->menu_name = 'product_page_menu';
		$this->contact_form_id = 'CF54d702af07cef';

		$this->cf = $this->pod->field( 'cf_add_on');

		if ( get_post_meta( $this->post->ID, 'edd_coming_soon', true  ) ) {
			$this->coming_soon = true;
		}

		if ( isset( $_GET[ 'preview' ] ) && $_GET[ 'preview' ] ) {
			$this->coming_soon = false;
		}

	}

	/**
	 * Set header atts;
	 *
	 * @return mixed
	 */
	protected function header_atts() {
		$fields = array(
			'tagline' => 'product_tagline',
			'header_bg' => 'header_image',
			'title' => 'post_title',
		);

		if ( $this->free ) {
			unset( $fields[ 'header_bg' ] );
			$header_bg = wp_get_attachment_image_src( (int) get_post_thumbnail_id( $this->post->ID ), 'large' );
			if ( 1 > $header_bg ) {
				$header_bg = wp_get_attachment_image_src( cwp_theme_cwp_logo_id() );
			}
			$atts['header_bg'] = $header_bg[0];
		}

		foreach ( $fields as $key => $field ) {
			$atts[ $key ] = $this->pod()->display( $field );
		}

		$atts [ 'header_size' ] = '30%';
		$atts[ 'logo' ] = $this->logo;

		return $atts;

	}

	/**
	 * Construct and cache main page content
	 *
	 * @return array|bool|mixed|string
	 */
	public function page() {
		$key = md5( __CLASS__ . $this->post->ID );
		if ( false == ( $page = wp_cache_get( $key ) )  ) {
			$page[] = $this->post_content();
			if ( $this->coming_soon || $this->cf || true === $this->free ) {

			}else{
				$page[] = $this->feature_section();
			}
			
			$page[] = $this->testimonials_section();
			if ( false === $this->free ) {
				$page[] = $this->price_table();
			}

			$page[] = $this->contact_section();
			if ( ! $this->coming_soon ) {
				$page[] = $this->docs();
			}
			$page = implode( '', $page );
			wp_cache_set( $key, $page, '', 399 );
		}

		return $page;

	}

	/**
	 * Features Data
	 *
	 * @return array
	 */
	private function features() {

		$features = array();
		for ( $i = 1; $i <= 3; $i++) {

			foreach(
				array(
					'title',
					'text',
					'learn_more_link_text',
					'learn_more',
					'image'
				) as $field ) {
				$the_field = 'benefit_' . $i . '_' . $field;
				if ( 'learn_more' == $field  ) {
					$value = get_permalink( $this->pod->field( $the_field . '.ID' ) );
				}elseif( 'image' == $field) {
					$id = $this->pod->field( $the_field . '.ID' );
					$value = array(
						wp_get_attachment_url( $id ),
						get_post_meta( $id, '_wp_attachment_image_alt', true )
					);
				}else{
					$value = $this->pod->display(  $the_field  );
				}

				$features[ $i ][ $field ] = $value;

			}

		}

		return $features;

	}

	/**
	 * Create HTML for the features section.
	 *
	 * @return string
	 */
	public function feature_section() {
		$out[] = '<!--Features Section--><section class="feature-styles" id="features" >';
		$i = 0;
		foreach( $this->features() as $feature) {
			$left = false;
			if ( 1 == $i ) {
				$left = true;
			}
			$out[] = $this->feature( $feature, $left );
			$i++;
		}

		$out[] = '</section>';
		return implode( '', $out );

	}

	/**
	 * Create HTML for a specific header
	 *
	 * @param array $data Feature data.
	 * @param bool $left Push?
	 *
	 * @return string
	 */
	protected function feature( $data, $left ) {
		if ( ! $data[ 'text' ] || '' == $data[ 'text' ] || ! $data[ 'title' ] || '' == $data[ 'title' ]) {
			return '';
		}
		
		$image = sprintf( '<img class="plugin-features-image" src="%1s" alt="%2s"  />', $data[ 'image' ][0], $data[ 'image' ][1] );
		$link = sprintf(
			'<a href="%1s" title="%2s">%3s</a>',
			esc_url( $data['learn_more'] ),
			esc_attr( $data['learn_more_link_text'] ),
			$data[ 'learn_more_link_text' ]
		);

		$image = sprintf(
			'<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 text-center">
					%1s
				</div>', $image
		);

		$text = sprintf(
			'<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 feature-text">
				<h1>%1s</h1>
				<div class="txt">%2s</div>
				<div class="getstarted">%3s</div>
			</div>',
			$data[ 'title' ],
			$data[ 'text' ],
			$link
		);

		if ( $left ) {
			$content = $image.$text;
		}else{
			$content = $text.$image;
		}

		return '<div class="container feature-container">' . $content . '</div>';



	}

	/**
	 * Get pricing details.
	 *
	 * @return array
	 */
	protected function pricing() {
		$prices = edd_get_variable_prices( $this->post->ID );

		$args = array(
			'download_id' => $this->post->ID,
			'text'        => __( 'Choose', 'cwp-theme' ),
		);


		foreach( $prices as $i => $price ) {


			$args[ 'price_id' ] = (string) $i;

			//$link = edd_get_purchase_link( $args  );
			$link = add_query_arg( array(
				'edd_action' => 'add_to_cart',
				'download_id' => $this->post->ID,
				'edd_options[price_id]' => (string) $i,
			), home_url( 'checkout' ) );

			$link = sprintf( '<a href="%1s" class="purchase-button">Choose</a>', $link);
			$link = sprintf( '<div id="purchase-%1s">%2s</div>', $i, $link );

			$prices[ $i ][ 'link' ] = $link;
			$prices[ $i ][ 'sites' ] = $price[ 'name' ];

		}

		$prices[ 1 ][ 'level' ] = __( 'Personal', 'cwp-theme' );
		$prices[ 2 ][ 'level' ] = __( 'Business', 'cwp-theme' );
		$prices[ 3 ][ 'level' ] = __( 'Developer', 'cwp-theme' );

		return $prices;
	}

	/**
	 * Output a pricing table's HTML
	 *
	 * @return string|void
	 */
	public function price_table() {
		if ( $this->coming_soon ) {
			return;
		}

		$out[] = '<script>jQuery( document ).ready(function() {jQuery( ".edd-add-to-cart-label" ).html( "Choose" );});</script>';
		$out[] = sprintf( '<!--Pricing Table Section--><section id="pricing"><div class="container">
<div class="container">%1s</div>', $this->purchase_cta());
		$i = 1;
		foreach( $this->pricing() as $price ) {
			$out[] = sprintf(
				'<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 pricing-box">
					<div id="price-%0s" class="bggray price">
						<div class="package">%1s</div>
						<div class="divider"></div>
						<div class="amount">$%2s</div>
						<div class="duration">%3s</div>
					</div>
					<div class="featcontent">
						%4s
					</div>
				</div>',
				$i,
				$price[ 'level' ],
				$price[ 'amount' ],
				$price[ 'sites' ],
				$price[ 'link' ]
			);
			$i++;

		}

		$out[] = '</div></section>';

		return implode( '', $out );

	}

	/**
	 * The CTA before pricing tables
	 *
	 * @return string
	 */
	protected function purchase_cta() {
		$cta = array( 'header', 'text' );
		$out = false;
		foreach( $cta as $field ) {
			if ( ! is_null( $value = $this->pod->display( 'cta_header_' . $field ) ) ) {
				$out[] = '<div class="pricing-cta-'.$field.' >' . $value . '</div>';
			}
		}

		if ( $out ) {
			return implode( '', $out );
		}


	}

	/**
	 * Output docs section
	 *
	 * @return string
	 */
	public function docs() {

		if ( $this->cf ) {
			$docs = $this->pod->template( 'After Download' );

		}elseif( 474 === $this->post->ID || 'caldera-forms' == $this->post->post_name ){
			$docs = __( sprintf( '<h5><a href="#" title="Caldera Forms Docs Site" target="_blank">Docs For Caldera Forms Are Available at docs.CalderaForms.com</a></h5>', 'http://docs.cadleraforms.com' ), 'cwp-theme'  );
		} else{
			$docs = cep_render_easy_pod( 'auto_docs_list' );
		}
		return sprintf(
			'<!--Docs Links -->
				<section id="docs">
					<div class="container">
						<h1>Documentation</h1>
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							%1s
						</div>
					</div>
				</section>', $docs
		);


	}


}
