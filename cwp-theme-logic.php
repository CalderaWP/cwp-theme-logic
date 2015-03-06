<?php
/*
 Plugin Name: Logic for CalderaWP Theme
 */

/**
 * Include classes
 */
add_action( 'init', function() {
	include( dirname( __FILE__ ) . '/classes/CWP_Theme_Caldera_Answers.php' );
	include( dirname( __FILE__ ) . '/classes/CWP_Theme_Data.php' );

	include( dirname( __FILE__ ) . '/classes/CWP_Theme_Front_Page_Data.php' );
	include( dirname( __FILE__ ) . '/classes/CWP_Theme_Front_Page.php' );

	include( dirname( __FILE__ ) . '/classes/CWP_Theme_Docs.php' );

	include( dirname( __FILE__ ) . '/classes/CWP_Theme_Social.php' );

});

/**
 * HTML markup for link to the WPORG page for CF
 *
 * @param bool|string $text Optionak
 *
 * @return string
 */
function cwp_theme_cf_wporg_link( $text = false) {
	if ( ! $text ) {
		$text = 'Caldera Forms';
	}

	return sprintf( '<a title="Caldera Forms WordPress.org Download Page" href="https://wordpress.org/plugins/caldera-forms/" target="_blank" >%1s</a>', $text );

}

/**
 * Disable comments on all post types besides post
 */
add_action( 'init', function() {
	$post_types = get_post_types( array( 'public' => true ) );
	foreach( $post_types as $post_type ) {
		if ($post_type == 'post' ) {
			continue;
		}

		if ( post_type_supports( $post_type, 'comments' ) ) {
			remove_post_type_support( $post_type, 'comments' );
		}

	}

	add_filter( 'comments_open', function( $open, $post )  {
		if ( 'post' != get_post_type( $post ) ) {
			$open = false;
		}

		return $open;
	},50, 2 );

});

/**
 * Register widget area to be used on EDD pages
 */
add_action( 'widgets_init', array( cwp_theme_get_edd_class(), 'edd_widget_area' ) );
function cwp_theme_get_edd_class() {
	include( dirname( __FILE__ ) . '/classes/CWP_Theme_EDD_Product_IDs.php' );
	include( dirname( __FILE__ ) . '/classes/CWP_Theme_EDD.php' );

	return \CWP_Theme_EDD::init();

}

/**
 * Randomize Caldera Answers Easy Pods results
 */
add_filter( 'caldera_easy_pods_query_params', function( $params, $pod, $tags, $easy_pod_slug ) {
	if ( 'caldera_answers' == $easy_pod_slug || 'answers_widget' == $easy_pod_slug ) {
		$params[ 'orderby' ] = 'rand()';
	}

	return $params;

}, 10, 4);


/**
 * Put an excerpt and additional markup on code snippets.
 */
add_filter( 'dsgnwrks_snippet_display', function( $snippet_html, $atts, $snippet ) {
	$excerpt = '';
	if ( $snippet->post_excerpt ) {
		$excerpt = sprintf( '<div class="code-snippet-description">%1s</div>', wpautop( $snippet->post_excerpt ) );
	}

	$html = sprintf( '<div class="cwp-code-snippet">%1s %2s </div>', $excerpt, $snippet_html );

	return $html;


}, 10, 3 );

/**
 * Hook into the_content
 */
add_action( 'init',
	function() {
		add_filter( 'the_content',
			function( $content ) {
				if ( is_page( CWP_Theme_Docs::$docs_page_id ) ) {
					$content = CWP_Theme_Docs::content_filter( $content );
				}

				return $content;

			},
			35 );

	},
	35 );

/**
 * Remove EDD's microdata on post title in the "After Download" Pods Template
 */
add_filter( 'pods_templates_pre_template',
	function( $code, $template ) {
		if ( isset( $template[ 'name'] ) && 'After Download' == $template[ 'name'] ) {
			remove_filter( 'the_title', 'edd_microdata_title', 10, 2 );
		}

		return $code;

	}, 10, 2
);

/**
 * Re-enable EDD's microdata on post title after running the "After Download" Pods Template
 */
add_filter( 'pods_templates_post_template',
	function( $code, $template ) {
		if ( isset( $template[ 'name'] ) && 'After Download' == $template[ 'name'] ) {
			add_filter( 'the_title', 'edd_microdata_title', 10, 2 );
		}

		return $code;

	}, 10, 2
);

/**
 * Increase upload limits for admins
 */
add_filter( 'upload_size_limit', function( $limit ) {
	if ( current_user_can( 'edit_options' ) ) {
		return 8000000;

	}

	return $limit;

});

/**
 * Bio shortcode
 */
add_shortcode( 'cwp_bio', 'cwp_bio_shortcode' );
function cwp_bio_shortcode( $atts, $content = '' ) {
	$atts = shortcode_atts( array(
		'who' => 'david',
	), $atts, 'cwp_bio' );


	return cwp_bio_box( $atts[ 'who' ], $content );
}

/**
 * Show a bio, with gravatar and social links.
 *
 * @param string $who Whose bio david|josh
 * @param string $bio The actual bio content.
 *
 * @return string|void
 */
function cwp_bio_box( $who, $bio ) {
	$data = CWP_Theme_Social::our_data( $who );

	if ( is_array( $data ) ) {
		$name = $data['name'];


		$social_html = CWP_Theme_Social::social_html( $data[ 'social' ], $name );

		$out[] = '<div class="about-box">';
		$out[] = sprintf( '<div class="about-left">%1s %2s</div>',
			'<div class="gravatar-box">' . get_avatar( $data['gravatar'] ) . '</div>',
			'<div class="social">' . $social_html . '</div>'
		);
		$out[] = '<div class="about-right"><div class="bio">'.$bio.'</div></div>';
		$out[] = '</div>';
		$out[] = '<div class="clear"></div>';

		$out = implode( '', $out );

		$out = str_replace( 'Pods Framework', '<a href="http://Pods.io" title="Pods -- WordPress Custom Content Types and Fields" target="_blank">Pods Framework</a>', $out );


		return $out;

	}


}

/**
 * Gets current instance of the Theme Data class
 *
 * @return \CWP_Theme_Data|\CWP_Theme_Plugin_Page
 */
function cwp_theme_data() {
	if ( is_single() || is_page() ) {
		global $post;
		if ( cwp_theme_is_plugin_page() ) {
			global $plugin_data;

			if ( ! is_object( $plugin_data ) ) {
				include_once( dirname( __FILE__ ) . '/classes/CWP_Theme_Plugin_Page.php' );
				$plugin_data = new CWP_Theme_Plugin_Page( $post );
			}

			return $plugin_data;

		} elseif( 'course' === get_post_type() ) {
			global $course_data;

			if ( ! is_object( $course_data ) ) {
				include_once( dirname( __FILE__ ) . '/classes/CWP_Theme_Course_Data.php' );
				$course_data = new CWP_Theme_Course_Theme_Data( $post, false, true );
			}

			return $course_data;

		} else {
			global $single_post_data;
			if ( ! is_object( $single_post_data ) ) {
				$single_post_data = new CWP_Theme_Data( $post );
			}

			return $single_post_data;

		}

	}else{
		global $archive_data;
		if ( ! is_object( $archive_data ) ) {
			include( dirname( __FILE__ ) . '/classes/CWP_Theme_Archive_Data.php' );
			$archive_data = new CWP_Theme_Archive_Data();
		}

		return $archive_data;

	}

}

/**
 * Determine if is a plugin page
 *
 * @return bool
 */
function cwp_theme_is_plugin_page() {
	if ( is_single() && in_array( get_post_type(), array( 'free_plugin', 'download' ) ) ) {
		return true;
	}

}

/**
 * Get ID of out logo
 *
 * @return int
 */
function cwp_theme_cwp_logo_id( $white = false ) {
	if ( $white ) {
		return 816;
	}

	return 793;

}

/**
 * Add excerpts and thumbnails for the  page post type
 */
add_action( 'init', function() {
	add_post_type_support( 'page', array( 'excerpt', 'thumbnail' ) );

});

/**
 * Add analytics
 */
add_action( 'wp_head', function(){
	?>
	<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		ga('create', 'UA-59323601-1', 'auto');
		ga('send', 'pageview');

	</script>
<?php
});

/**
 * Show the featured plugins grid
 *
 * @return string
 */
function cwp_theme_featured_plugins() {
	/**
	 * Runs before featured plugin thing. Useful for preventing multiple outputs of the thing.
	 */
	do_action( 'cwp_theme_featured_plugins' );
	$out = '<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="featured">
				<h3>Featured Plugins</h3>
				<div class="block-grid-3">
					'.  cep_render_easy_pod( 'featured_products' ) . '
               </div>
               <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 all-plugins">
                  <a href="'. esc_url( home_url( 'plugins' ) ) .'" class="button big-cta">See All Plugins</a>
               </div>

            </div>';
	return $out;

}

/**
 * Show the featured plugins grid--only if it hasn't been shown already.
 *
 * @return string
 */
function cwp_theme_featuted_plugins_once() {
	if ( ! did_action( 'cwp_theme_featured_plugins' ) ) {
		return cwp_theme_featured_plugins();

	}

}

/**
 * Product Buy Shortcode
 */
add_shortcode( 'cwp-product-buy', 'cwp_product_buy_shortcode' );
function cwp_product_buy_shortcode( $atts ) {

	if ( isset(  $atts[ 'ID' ] ) ) {
		return CWP_Theme_Docs::link_box( $atts[ 'ID' ] );
	}

}

/**
 * Set Paypal EDD no shipping.
 */
add_filter( 'edd_paypal_redirect_args', function( $paypal_args ) {
	$paypal_args[ 'no_shipping' ] = '2';
	return $paypal_args;
});

/**
 * Allow "download" and "free_plugin" to be publically queryable via the PODS JSON API
 */
add_filter( 'pods_json_api_access_pods_get_items', function( $access, $method, $pod ) {
	if ( in_array(  $pod, array( 'free_plugin', 'download' ) ) ) {
		$access = true;
	}

	return $access;

}, 50, 3 );

