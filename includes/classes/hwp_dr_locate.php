<?php
/**
 * Preforms location queries
 *
 * @package   @jpdr
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 */

class hwp_dr_locate {
	static $cache_group = 'hwp_dr-locations';

	/**
	 * Get locations with range of the $range param
	 *
	 * @param array  $lat_long Location to search from.
	 * @param int    $range	Range to search within.
	 * @param string $source The name of the content type to search in.
	 * @param string $source_typeThe content type of $source. post|pod
	 *
	 * @return array|bool|mixed|string|void
	 */
	public function get_locations( $lat_long, $range, $source = 'location', $source_type = 'pod' ) {
		$params = $this->params( $lat_long, $range, $source, $source_type );
		$locations = $this->query_locations( $params, $source_type );

		if ( is_array( $locations ) ) {
			return $locations;
		}
		else {
			return __( 'No locations found.', 'hwp_dr' );
		}

	}

	/**
	 * Does the actual query for locations
	 *
	 * @param array $params
	 * @param string $type post|pod
	 *
	 * @return array|bool|mixed
	 */
	function query_locations( $params, $type ) {

		$source = apply_filters( 'hwp_dr_location_source', 'location' );
		if ( $type === 'pod' && function_exists( 'pods') ) {
			$locations = $this->query_pod( $source, $params );
		}
		elseif ( $type !== 'pod' ) {
			$locations = $this->query_post_type( $source, $params );
		}
		else {
			wp_die( __METHOD__.__LINE__ );
		}

		if ( is_array( $locations ) ) {


			return $locations;

		}

	}

	/**
	 * Params that can be passed to Pods::find() or (theoretically) $wpdb->get_col()
	 *
	 * @param array $lat_long Location to search from.
	 * @param int    $range	Range to search within.
	 *
	 * @return array
	 */
	function params( $lat_long, $range ) {
		if ( ! empty( $lat_long ) && ! empty( $lat_long[ 'lat' ] ) && ! empty( $lat_long[ 'long' ] ) && $range ) {

			$lat = $lat_long[ 'lat' ];
			$long = $lat_long[ 'long' ];

			//@TODO Document filter
			$delivery_range_field = $this->fields( 'delivery_range' );
			$geocode_lat_field = $this->fields( 'lat' );

			$geocode_long_field = $this->fields( 'long' );

			//@TODO add possibility that this could be table storage Pod/ACT
			//@TODO ensure this works with WPDB
			$params = array(
				'select' => 't.*, ( 3959 * acos( cos( radians( ' . $lat . ' ) ) * cos( radians( geocode_lat.meta_value ) ) * cos( radians( geocode_long.meta_value ) - radians( ' . $long . ' ) ) + sin( radians( ' . $lat . ' ) ) * sin( radians( geocode_lat.meta_value ) ) ) ) AS `distance`',
				'limit' => -1,
				'having' => '`distance` <= ' . $range . ' OR `distance` IS NULL',
				'orderby' => '`distance`',
				'pagination' => false,
				'search' => false,
				'expires' => ( 60 * 60 * 24 ),
				'where' => "0 < LENGTH( {$delivery_range_field}.meta_value ) AND {$geocode_lat_field}.meta_value IS NOT NULL AND {$geocode_long_field}.meta_value IS NOT NULL AND `t`.`post_status` = \"publish\"",
			);

			return $params;
		}

	}


	/**
	 * Query a Pod by location/range
	 *
	 * @param string|Pods $pod Either the name of the Pod to query or a prebuilt object of the Pods class.
	 * @param array $params
	 *
	 * @return array|bool|mixed
	 */
	function query_pod( $pod, $params ) {
		if ( ! is_object( $pod ) || is_pod( $pod ) ) {
			if ( is_string( $pod ) ) {
				$pod = pods( $pod );
			}
			else {
				wp_die( __METHOD__.__LINE__ );
			}
		}

		$pod->find( $params );

		if ( 0 < $pod->total() ) {

			$map_locations = array();
			if ( isset( $map_center ) ) {
				unset( $map_center );
			}

			if ( isset( $map_zoom ) ) {
				unset( $map_zoom );
			}

			$location_cache = wp_cache_get( md5( json_encode( $params )  ), self::$cache_group );

			if ( empty( $location_cache ) ) {
				while ( $pod->fetch() ) {
					$address = array();

					$address[] = trim( $pod->field( 'address_1' ) );

					$address = array_filter( $address );

					$address = str_replace( ', <br />,', '<br />', implode( ', ', $address ) );

					$title = array();

					$title[] = trim( $pod->field( 'post_title' ) );
					$title[] = trim( $pod->field( 'state_province' ) );

					if ( 'USA' != $pod->field( 'country' ) )
						$title[] = trim( $pod->field( 'country' ) );

					$title = array_filter( array_unique( $title ) );
					$title = str_replace( ',  , ', ', ', implode( ', ', $title ) );

					$google_address = array();

					$google_address = hwp_dr_address( $pod->id() );

					if ( isset( $map_locations_secondary[ $pod->id() ] ) ) {
						unset( $map_locations_secondary[ $pod->id() ] );
					}

					$lat = $this->get_lat( $pod, $google_address );
					$long = $this->get_long( $pod, $google_address );
					$map_locations[ $pod->id() ] = array(
						'title' => $title,
						'address' => $address,
						'phone' => $pod->field( 'phone_number' ),
						'ID' => $pod->id(),
						'google_address' => $google_address,
						'lat' => $lat,
						'long' => $long,
						'distance' => ltrim( ltrim( number_format( (float) $pod->field( 'delivery_range' ), 1 ), '0' ), '.' ) . ' mile' . ( 1 >= (float) $pod->field( 'delivery_range' ) ? '' : 's' )
					);
				}

				wp_cache_set( md5( json_encode( $params ) ), $map_locations, self::$cache_group, ( 60 * 60 * 24 ) );
			}
			else {
				$map_locations = $location_cache;

				foreach ( $map_locations as $id => $location ) {
					if ( isset( $map_locations_secondary[ $id ] ) )
						unset( $map_locations_secondary[ $id ] );
				}
			}
		}

		if ( isset( $map_locations ) && is_array( $map_locations ) ) {
			return $map_locations;
		}

	}

	/**
	 * Get latitude from field value or geocode it
	 *
	 * @param $pod
	 * @param $address
	 *
	 * @return array|mixed
	 */
	function get_lat( $pod, $address ) {
		$lat = $pod->field( 'geocode_lat', true );
		if ( ! $lat ) {
			$lat = $this->geocoder_class()->geocode_address( $address );
			$lat = pods_v( 'lat', $lat );
			if ( $lat ) {
				$pod->save( 'geocode_lat', $lat );
			}

		}

		return $lat;

	}

	/**
	 * Get longitude from field value or geocode it
	 *
	 * @param $pod
	 * @param $address
	 *
	 * @return array|mixed
	 */
	function get_long( $pod, $address ) {
		$long = $pod->field( 'geocode_long', true );
		if ( ! $long ) {
			$long = $this->geocoder_class()->geocode_address( $address );
			$long = pods_v( 'long', $long );
			if ( $long ) {
				$pod->save( 'geocode_lat', $address );
			}

		}

		return $long;
	}

	function geocoder_class() {

		return hwp_dr_geocode::init();

	}

	/**
	 * @param $post_type
	 * @param $params
	 *
	 * @return array
	 */
	function query_post_type( $post_type, $params ) {
		wp_die( __METHOD__.'not ready:(');
		global $wpdb;
		return $wpdb->get_col( $params );
	}

	/**
	 * Get the name of field being used for delivery_range, latitude or longitude.
	 *
	 * @param string $field Options: delivery_range|lat|long
	 *
	 * @return mixed|void
	 */
	function fields( $field ) {

		if ( ! in_array( $field, array( 'delivery_range', 'lat', 'long' )  ) ) {
			wp_die( __METHOD__.__LINE__ );
		}

		if ( $field === 'delivery_range' ) {

			return apply_filters( 'hwp_dr_delivery_range_field', 'delivery_range' );

		}

		if ( $field === 'lat' ) {

			return apply_filters( 'hwp_dr_geocode_lat_field', 'geocode_lat' );

		}

		if ( $field === 'long' ) {

			return apply_filters( 'hwp_dr_geocode_long_field', 'geocode_long' );

		}

	}

	/**
	 * Holds the instance of this class.
	 *
	 * @since  0.0.1
	 * @access private
	 * @var    object
	 */
	private static $instance;


	/**
	 * Returns the instance.
	 *
	 * @since  0.0.1
	 * @access public
	 * @return object
	 */
	public static function init() {

		if ( !self::$instance )
			self::$instance = new hwp_dr_locate();

		return self::$instance;
	}
}
