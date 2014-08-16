<?php

/**
 * Class hwp_dr_geocode
 *
 *  Geocoding related functionality
 *
 *  @since 0.0.1
 */
class hwp_dr_geocode {
	static $api_provider = 'google';
	static $api_client_id = '';
	static $cache_group = 'hwp_dr';

	/**
	 * Geocode a specific address
	 *
	 * @param string|array $address Address information
	 *
	 * @return array lat and long values
	 *
	 * @public
	 * @static
	 * @since 1.1
	 */
	public static function geocode_address ( $address ) {
		$lat = $long = $formatted_address = '';

		if ( ! empty( $address ) ) {
			if ( is_array( $address ) ) {
				$address = implode( ', ', $address );
			}

			//don't get from cache while in dev mode
			if ( ! defined( 'hwp_dr_DEV_MODE' ) || ! hwp_dr_DEV_MODE ) {
				$lat_long = wp_cache_get( 'hwp_dr_geocode_get_' . md5( $address ), self::$cache_group );
			}

			if ( !empty( $lat_long ) && is_array( $lat_long ) && !empty( $lat_long[ 'lat' ] ) && !empty( $lat_long[ 'long' ] ) ) {
				return $lat_long;
			}

			if ( 'google' == self::$api_provider ) {
				$key = '';

				if ( ! empty( self::$api_client_id ) ) {
					$key = '&client=' . urlencode( self::$api_client_id );
				}

				$json = @file_get_contents( 'http://maps.google.com/maps/api/geocode/json?address=' . urlencode( $address ) . '&sensor=false' . $key );

				if ( !empty( $json ) ) {
					$json = @json_decode( $json );

					if ( is_object( $json ) ) {
						$lat = $json->{'results'}[ 0 ]->{'geometry'}->{'location'}->{'lat'};
						$long = $json->{'results'}[ 0 ]->{'geometry'}->{'location'}->{'lng'};
						$formatted_address = $json->{'results'}[ 0 ]->{'formatted_address'};
					}
					else {
						return false;
					}
				}
				else {
					return false;
				}
			}
			else {
				return false;
			}
		}

		$lat_long = array(
			'lat' => $lat,
			'long' => $long,
			'address' => $formatted_address
		);

		wp_cache_set( 'hwp_dr_geocode_get_' . md5( $address ), $lat_long, self::$cache_group );

		if ( $address != $formatted_address ) {
			wp_cache_set( 'geocode_get_' . md5( $formatted_address ), $lat_long, self::$cache_group );
		}

		return $lat_long;
	}

	/**
	 * Get and format address
	 *
	 * @param int $id ID of item to get address for.
	 * @param null|Pods $pod
	 * @param bool $array
	 *
	 * @return array|string
	 */
	public static function address( $id, $pod = null, $array = false ) {
		$sources = self::address_sources();

		if ( is_array( $sources )  ) {
			if ( ! is_null( $pod ) ) {
				if ( $pod->id() !== $id || $pod->id === 0 || is_null( $pod->id ) ) {
					$pod->fetch( $id  );
				}
			}

			foreach ( $sources as $field => $type ) {
				if ( $type === 'meta' ) {
					$address[] = trim( get_post_meta( $id, $field, true ) );
				}
				elseif( $type === 'pod' ) {
					$address[] = trim( $pod->field( $field ) );
				}
				else {
					wp_die( __METHOD__.__LINE__ );
				}

			}

			$address = array_filter( array_unique( $address ) );
			if ( $array ) {
				$address = implode( ', ', $address );
			}

			return $address;

		}
	}

	/**
	 * Provides sources for address. Use its filter to change from defaults.
	 *
	 * @return array
	 */
	private static function address_sources() {
		$sources = array(
			'address_1' 		=> 'meta',
			'address_2' 		=> 'meta',
			'city'				=> 'meta',
			'state_providence'	=> 'meta',
			'postal_code'		=> 'meta',
			'country'			=> 'meta',
		);

		/**
		 * Override field names and field types for building addresses
		 *
		 * @param array $sources An array of fields to base address on, in order. use the form of field_name => 'field_type'. For 'field_type' use 'meta' if source is a post type, or 'pods' if source is a Pod using table-storage methods.
		 *
		 * @return array
		 *
		 * @since 0.0.1
		 */
		$sources = apply_filters( 'hwp_dr_address_sources', $sources );

		return $sources;
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
	 * Returns an isntance of this class
	 *
	 * @return hwp_dr_geocode|object
	 */
	public static function init() {
		if ( !self::$instance )
			self::$instance = new self;

		return self::$instance;

	}

} //end hwp_dr_geocode
