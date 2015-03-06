<?php
/**
 * Data for archive pages
 *
 * @package   cwp-theme
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 Josh Pollock
 */

class CWP_Theme_Archive_Data extends CWP_Theme_Data {

	public $header_atts;

	public $logo;

	public function __construct() {

		$this->logo = cwp_theme_cwp_logo_id( true );
		$this->header_atts = $this->header_atts();
	}

	public function header_atts() {
		$atts = array(
			'tagline' =>  get_bloginfo( 'description' ),
			'header_bg' => wp_get_attachment_image_src( cwp_theme_cwp_logo_id( false ), 'large' ),
			'title' => get_bloginfo( 'title' ),
			'header_size' => '250px',
			'logo' => wp_get_attachment_image( $this->logo, 'thumbnail' ),
		);

		$atts[ 'header_bg' ] = $atts[ 'header_bg' ][0];

		return $atts;

	}

}
