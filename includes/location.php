<?php
/**
 * DON'T USE THIS. Leaving it here for now as its unedited stuff from Scott that the rest is based on.
 */
if ( !empty( $lat_long ) && !empty( $lat_long[ 'lat' ] ) && !empty( $lat_long[ 'long' ] ) ) {
	$map_locations = array();

	$lat = $lat_long[ 'lat' ];
	$long = $lat_long[ 'long' ];

	$params = array(
		'select' => 't.*, ( 3959 * acos( cos( radians( ' . $lat . ' ) ) * cos( radians( geocode_lat.meta_value ) ) * cos( radians( geocode_long.meta_value ) - radians( ' . $long . ' ) ) + sin( radians( ' . $lat . ' ) ) * sin( radians( geocode_lat.meta_value ) ) ) ) AS `distance`',
		'limit' => -1,
		'where' => 'coming_soon.meta_value = 0 AND geocode_lat.meta_value IS NOT NULL AND geocode_long.meta_value IS NOT NULL AND `t`.`post_status` = "publish"',
		'having' => '`distance` <= ' . $distance . ' OR `distance` IS NULL',
		'orderby' => '`distance`',
		'pagination' => false,
		'search' => false,
		'expires' => ( 60 * 60 * 24 ) // 24 hours
	);

	if ( is_page( 'order-online' ) )
		$params[ 'where' ] = '0 < LENGTH( order_url.meta_value ) AND coming_soon.meta_value = 0 AND geocode_lat.meta_value IS NOT NULL AND geocode_long.meta_value IS NOT NULL AND `t`.`post_status` = "publish"';

	$pod->find( $params );

	if ( 0 < $pod->total() ) {
		if ( isset( $map_center ) )
			unset( $map_center );

		if ( isset( $map_zoom ) )
			unset( $map_zoom );

		$location_cache = wp_cache_get( md5( json_encode( $params ) . ( is_page( 'order-online' ) ? '-order-online' : '' ) ), 'naked-locations' );

		if ( empty( $location_cache ) ) {
			while ( $pod->fetch() ) {
				$address = array();

				$address[] = trim( $pod->field( 'address_1' ) );
				//$address[] = trim( $pod->field( 'address_2' ) );

				$address = array_filter( $address );

				/*if ( 0 < count( $address ) )
					$address[] = '<br />';

				$address[] = trim( $pod->field( 'city' ) );
				$address[] = trim( $pod->field( 'state_province' ) );
				$address[] = trim( $pod->field( 'postal_code' ) );

				$address = array_filter( $address );

				if ( 0 < count( $address ) && 0 < strlen( $pod->field( 'country' ) ) )
					$address[] = '<br />';

				$address[] = trim( $pod->field( 'country' ) );

				$address = array_filter( $address );*/
				$address = str_replace( ', <br />,', '<br />', implode( ', ', $address ) );

				$title = array();

				$title[] = trim( $pod->field( 'post_title' ) );
				$title[] = trim( $pod->field( 'state_province' ) );

				if ( 'USA' != $pod->field( 'country' ) )
					$title[] = trim( $pod->field( 'country' ) );

				$title = array_filter( array_unique( $title ) );
				$title = str_replace( ',  , ', ', ', implode( ', ', $title ) );

				$order_now = $pod->field( 'order_url' );

				$google_address = array();

				$google_address[] = trim( get_post_meta( get_the_ID(), 'address_1', true ) );
				$google_address[] = trim( get_post_meta( get_the_ID(), 'address_2', true ) );
				$google_address[] = trim( get_post_meta( get_the_ID(), 'city', true ) );
				$google_address[] = trim( get_post_meta( get_the_ID(), 'state_province', true ) );
				$google_address[] = trim( get_post_meta( get_the_ID(), 'postal_code', true ) );
				$google_address[] = trim( get_post_meta( get_the_ID(), 'country', true ) );

				$google_address = array_filter( array_unique( $google_address ) );
				$google_address = implode( ', ', $google_address );

				if ( isset( $map_locations_secondary[ $pod->id() ] ) )
					unset( $map_locations_secondary[ $pod->id() ] );

				$map_locations[ $pod->id() ] = array(
					'title' => $title,
					'address' => $address,
					'phone' => $pod->field( 'phone_number' ),
					'link' => $pod->field( ( is_page( 'order-online' ) ? 'order_url' : 'detail_url' ) ),
					'order_now' => $order_now,
					'google_address' => $google_address,
					'lat' => $pod->field( 'geocode_lat', true ),
					'long' => $pod->field( 'geocode_long', true ),
					'distance' => ltrim( ltrim( number_format( (float) $pod->field( 'distance' ), 1 ), '0' ), '.' ) . ' mile' . ( 1 >= (float) $pod->field( 'distance' ) ? '' : 's' )
				);
			}

			wp_cache_set( md5( json_encode( $params ) ) . ( is_page( 'order-online' ) ? '-order-online' : '' ), $map_locations, 'naked-locations', ( 60 * 60 * 24 ) );
		}
		else {
			$map_locations = $location_cache;

			foreach ( $map_locations as $id => $location ) {
				if ( isset( $map_locations_secondary[ $id ] ) )
					unset( $map_locations_secondary[ $id ] );
			}
		}
		?>
		<section id="full-map-wrapper" class="grey-noise rd2-show-desktop rd2-show-tablet-large rd2-show-tablet-small">
			<div id="full-map"></div>
		</section>

		<?php include( 'includes/map.php' ); ?>

		<section id="post-<?php the_ID(); ?>" <?php post_class( 'inner' ); ?>>
			<h2 class="h1 grey">
                <span>
                    <span class="result-count"><?php echo count( $map_locations ); ?></span>
                    Result<?php echo ( 1 == count( $map_locations ) ? '' : 's' ); ?> for
                </span><br />
				<?php echo esc_html( $query ); ?>
			</h2>

			<div class="rd2-layout-fluid rd2-cols-3 rd2-r-tablet-large-50-50 rd2-r-tablet-small-50-50 rd2-r-mobile-large-stacked rd2-r-mobile-small-stacked">
				<?php
				foreach ( $map_locations as $location ) {
					?>
					<div class="rd2-layout-fluid rd2-cols-5">
						<div class="map-location-marker">
							<a href="<?php echo $location[ 'link' ]; ?>">
								<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/icon/map-marker.png" />
							</a>
						</div>

						<div class="rd2-span-4">
							<a href="<?php echo $location[ 'link' ]; ?>" class="map-location-title"><?php echo $location[ 'title' ]; ?></a>

							<div class="map-location-distance">
								<?php echo $location[ 'distance' ]; ?>
							</div>

							<div class="map-location-info">
                                <span class="map-location-address">
                                    <?php echo $location[ 'address' ]; ?>
                                </span>

								<br />

                                <span class="map-location-phone">
                                    <?php echo $location[ 'phone' ]; ?>
                                </span>
							</div>

							<div class="map-location-links">
								<?php
								if ( !empty( $location[ 'order_now' ] ) ) {
									?>
									<a href="<?php echo $location[ 'order_now' ]; ?>" class="np-btn">Order Now</a>
								<?php
								}
								?>

								<?php
								if ( rd2_is_mobile() ) {
									?>
									<a href="https://maps.google.com/maps?q=<?php echo urlencode( $location[ 'google_address' ] ); ?>" class="np-btn np-btn-grey">View Map</a>
								<?php
								}
								?>
							</div>
						</div>
					</div>
				<?php
				}
				?>
			</div>
		</section>
	<?php
	}
	else {
		?>
		<section id="full-map-wrapper" class="grey-noise rd2-show-desktop rd2-show-tablet-large rd2-show-tablet-small">
			<div id="full-map"></div>
		</section>

		<?php include( 'includes/map.php' ); ?>

		<section id="post-<?php the_ID(); ?>" <?php post_class( 'inner' ); ?>>
			<h2 class="h1 grey">
				<span><span class="result-count">0</span> Results for</span><br />
				<?php echo esc_html( $query ); ?>
			</h2>
		</section>
	<?php
	}
}
