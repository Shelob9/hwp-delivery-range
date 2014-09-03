<?php
/**
 * @TODO What this does.
 *
 * @package   @TODO
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

class hwp_dr_front_end {

	function __construct() {
		if ( false !== $this->page_id()  ) {
			add_filter( 'the_content', array( $this, 'output_to_page' ) );
			add_action( 'init', array( $this, 'get_results' ) );
		}

		//add_shortcode()
	}

	function output_to_page( $content ) {
		if ( is_page( $this->page_id() ) ) {
			$content = $this->full_output();

		}

		return $content;
		
	}

	function full_output() {
		$out[] = sprintf( '<div class="hwp-dr-search-form">%1s</div>', $this->search_form() );
		$out[] = $this->display_results();

		return sprintf( '<div class="hwp-dr" id="hwp-dr">%1s</div>', implode( $out ) );

	}

	function search_form() {
		if ( defined( 'PODS_VERSION' ) ) {
			wp_enqueue_style( 'pods-form', false, array(), false, true );

			if ( wp_script_is( 'pods', 'registered' ) && !wp_script_is( 'pods', 'done' ) ) {
				wp_print_scripts( 'pods' );
			}

			$pods_form = pods_form();

			//@TODO we don't need a nonce. Right?
			//@TODO filter for this?
			$fields = array(
				'location' => __( 'Your Location', 'hwp_dr' ),
				'range'	 => __( 'Range', 'hwp_dr' ),
			);


			foreach( $fields as $field => $label ) {
				$form_fields[] = $pods_form::label( $field, $label );
				$form_fields[] = $pods_form::field( $field, '', 'text' );
			}
			$form_fields = implode( $form_fields );
			$form = sprintf( '
				<div id="pods-meta-box" class="postbox" style="width:100%;">
					<form action="%1s" method="get">
						%2s
						<input type="submit" value="Select This Date And Park" class="pods-submit-button button" />
					</form>
				</div>
			', $this->page_url(), $form_fields );
		}
		else {
			$form = __( 'Error! We do not have a form for when Pods is not active . You can use the "hwp_dr_search_form" filter to add your own form.', 'hwp_dr' );
		}

		return apply_filters( 'hwp_dr_search_form', $form );

	}

	function query( $lat_long, $range, $source = 'location', $source_type = 'pod' ) {
		return $this->location_class()->get_locations( $lat_long, $range, $source, $source_type );
	}

	function display_results( $results = null ) {
		if ( is_null( $results ) ) {
			$results = $this->get_results();
		}

		$items = false;
		if ( is_array( $results ) ) {
			foreach( $results as $result ) {
				$item = false;
				$link = '';
				if ( isset( $result[ 'ID' ] ) ) {
					$link = get_permalink( $result[ 'ID' ] );
				}
				else {
					//@todo how to avoid bad things happening if ID ! isset?
				}

				if ( isset( $result[ 'title' ] ) ) {
					$title = $result[ 'title' ];
				}
				else {
					$title = get_the_title( $result[ 'ID' ] );
				}

				$item[] = sprintf( '<a href="%1s" title="$2s">%3s</a>', $link, $title, $title );
				$details = false;
				foreach( $this->detail_fields() as $field => $label ) {

					if ( isset( $result[ $field ] ) ) {

						$value = (string) $result[ $field ];
						if ( ! empty( $value  ) ) {
							$details[] = sprintf( '<li><span class="label hwp-dr-label">%1s</span> %2s</li>', $label, $value );
						}
					}
				}

				if ( is_array( $details ) ) {
					$item[] = sprintf( '<ul class="hwp-dr-result-details">%1s</ul>', implode( $details ) );
				}

				if ( is_array( $item ) ) {
					$items[] = sprintf( '<div class="hwp-dr-result">%1s</div>', implode( $item ) );
				}

				unset( $item );

			}
		}

		if ( is_array( $items ) ) {
			return sprintf( '<div class="hwp-dr-results">%1s</div>', implode( $items ) );

		}

	}

	function get_results() {
		if ( isset( $_GET[ 'location' ] ) && isset( $_GET[ 'range' ] ) )  {
			$location = $_GET[ 'location' ];
			$range = $_GET[ 'range' ];
			$location = $this->geocode( $location );
			if ( is_string( $range ) && is_array( $location ) ) {

				return $this->query( $location, $range );

			}

		}

	}

	function detail_fields() {

		$detail_fields = array(
			'address' => __( 'Address', 'hwp_dr' ),
			'phone' => __( 'Phone', 'hwp_dr' ),
		);

		return apply_filters( 'hwp_detail_fields', $detail_fields );

	}

	private function page_id() {

		return get_option( 'hwp_dr_page_id', false );

	}

	private function page_url() {

		if ( false !== ( $id = $this->page_id() ) ) {

			return get_permalink( $id );

		}

	}

	private function location_class() {

		return hwp_dr_locate::init();

	}

	private function geocoder_class() {

		return hwp_dr_geocode::init();

	}

	private function geocode( $address ) {

		return $this->geocoder_class()->geocode_address( $address );
	}


} 
