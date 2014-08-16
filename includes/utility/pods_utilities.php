<?php
global $type;
$type = apply_filters( 'hwp_dr_location_source', 'location' );
add_action( "pods_api_pre_save_pod_item_{$type}", 'hwp_dr_geocode_pod', 10, 3 );
function hwp_dr_geocode_pod( $pieces, $new, $id ) {
	$lat = $pieces[ 'fields' ][ 'geocode_lat' ][ 'value' ];
	$long = $pieces[ 'fields' ][ 'geocode_long' ][ 'value' ];
	if ( ! $lat || ! $long ) {
		$address = hwp_dr_address( $id );
		$geocoder = hwp_dr_geocode::init();
		$lat_long = $geocoder->geocode_address( $address );

		if ( is_array( $lat_long ) ) {
			$lat = pods_v( 'lat', $lat_long );
			$long = pods_v( 'long', $lat_long );

			$pieces[ 'fields' ][ 'geocode_lat' ][ 'value' ] = $lat;
			$pieces[ 'fields' ][ 'geocode_long' ][ 'value' ] = $long;

		}

	}

	return $pieces;
}

function hwp_dr_add_location_pod( $pod_name = 'location', $type = 'post_type', $storage = 'meta' ) {
	include_once trailingslashit( HWP_DR_PATH ).'includes/classes/hwp_dr_add_location_pod.php';

	return new hwp_dr_add_location_pod( $pod_name, $type, $storage );

}
