<?php
/**
 * Data for the courses page
 *
 * @package   cwp-theme
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 Josh Pollock
 */

class CWP_Theme_Course_Theme_Data extends CWP_Theme_Data {

	/**
	 * Get testimonials data from field
	 *
	 * @access protected
	 *
	 * @return array|mixed|null
	 */
	protected function testimonals_data() {
		$pods = $this->pod;
		$data = $pods->field( 'testimonials' );
		if ( $data ) {
			$data = explode( ' ', $data );
		}

		return $data;

	}

	/**
	 * Get ID for sign up form or fallback to default.
	 *
	 * @access protected
	 *
	 * @return string
	 */
	protected function form_id() {
		$value = $this->pod->field( 'sign_up_form_id' );
		if ( $value ) {
			$id = $value;
		}
		else{
			$id = 'CF54e6a9d813771';
		}

		return $id;

	}

	/**
	 * Get markup for form
	 *
	 * @return mixed|void
	 */
	public function sign_up_form() {
		return Caldera_Forms::render_form( $this->form_id() );

	}

}
