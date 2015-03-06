<?php
/**
 * Class for the documentation.
 *
 * @package   @cwp_theme
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 Josh Pollock
 */

/**
 * Class CWP_Theme_Docs
 */
class CWP_Theme_Docs {

	/**
	 * The ID for the Docs page
	 *
	 * @var int
	 */
	public static $docs_page_id = 469;

	/**
	 * Add some Easy Pods and other markup to the docs page
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public static function content_filter( $content ) {
		$slug  = pods_v_sanitized( 'product-slug' );
		$title = $docs = false;
		if ( $slug ) {
			$product_link = CWP_Theme_EDD::product_by_slug( $slug, true );
			if ( is_string( $product_link ) ) {
				$title = __( sprintf( 'All Docs For %1s', $product_link ), 'cwp-theme' );
				$docs  = cep_render_easy_pod( 'auto_docs_list' );
			}

		}

		if ( ! $title ) {
			$title = __( 'All Caldera WP Docs', 'cwp-theme' );
			$docs  = cep_render_easy_pod( 'all_docs' );
		}

		$title = '<h3>' . $title . '</h3>';

		$content = implode( '', array( $title, $docs, '<hr / >', $content ) );


		return $content;
	}

	/**
	 * Create the "Buy Now Box"
	 *
	 * @param int $id Either the post ID of current post, related to what to buy, or if $from_relationship = false, then the download's ID.
	 * @param bool $from_relationship Optional. If true, the default, then the download ID will be detrmined via the relationship. If false, then download ID is $id.
	 *
	 * @return string|void
	 */
	public static function link_box( $id, $from_relationship = true ) {
		if ( $from_relationship ) {
			$related = get_post_meta( $id, 'product' );

			if ( isset( $related[0] ) && is_array( $related[0] ) ) {
				$related = $related[0];
				$id      = $related['ID'];
			} else {
				return;
			}

		}

		if ( 'download' != get_post_type( $id ) ) {
			return;

		}


		$out[] = '<div class="container docs-plugin-link-box">';
		$out[] = '<div class="col-lg-4 col-md-4 hidden-xs hidden-sm">';
		$out[] = get_the_post_thumbnail( $id, 'medium' );
		$out[] = '</div>';
		$out[] = '<div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">';
		$out[] =  sprintf( '<h3>%1s</h3>', get_the_title( $id ) );
		$out[] = sprintf( '<p>%1s</p>', get_post_meta( $id, 'product_tagline', true ) );
		$link = get_permalink( $id );
		$link = esc_url( untrailingslashit( $link ) . '#pricing' );
		$out[] = sprintf('<a href="%1s" class="button">Buy Now</a>', $link );
		$out[] = '</div><div/><div class="clear"></div>';

		return implode( '', $out );

	}

}
