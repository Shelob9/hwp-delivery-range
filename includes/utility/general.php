<?php

function hwp_dr_address( $id, $pod = null ) {

	return hwp_dr_geocode::address( $id, $pod );

}

function hwp_dr_geocode( $address ) {

	return hwp_dr_geocode::geocode_address( $address );

}
