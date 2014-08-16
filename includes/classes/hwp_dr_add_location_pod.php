<?php

/**
 * Adds a Pod (either custom post type or advanced content type Pod)
 *
 * The function hwp_dr_add_location_pod() provides easy access to this class, which is not accessible by default.
 *
 * Class hwp_dr_add_location_pod
 */
class hwp_dr_add_location_pod {

	/**
	 * Constructor for this class.
	 *
	 * @param string $pod_name	Optional. Name for the Pod. Defaults to 'location'.
	 * @param string $type		Optional. Type of Pod options are 'pod' (for an advanced content type) and the default 'post_type' for a custom post type.
	 * @param string $storage	Optional. What storage type to use for a post_type pod. Options are the default, 'meta' and 'table' for table-based storage.
	 *
	 * @since 0.0.1
	 *
	 * @return int				The Pod ID.
	 */
	function __construct( $pod_name = 'location', $type = 'post_type', $storage = 'meta' ) {
		if ( $type === 'act' || $type === 'ACT' ) {
			$type = 'pod';
		}

		if ( in_array( $type, array( 'pod', 'post_type' ) ) ) {
			$this->activate_components( $type, $storage );
			$api = pods_api();
			$params = $this->pod_definition( $pod_name, $type, $storage );
			$pod_id = $api->save_pod( $params );
			$fields = $this->fields( $pod_name, $pod_id );
			foreach( $fields as $field => $params ) {
				$api->save_field( $params );
			}

			pods_cache_clear();
			pods_transient_clear();

			return $pod_id;

		}
		else {
			wp_die( $type.__METHOD__.__LINE__ );
		}
		
	}

	/**
	 * Defines the Pod
	 *
	 * @param string $pod_name name to use for Pod
	 * @param string $type Type of Pod. post_type|pod (use 'pod' for ACT
	 * @param string $storage Storage type for Pod. If $type === 'pod' must be storage.
	 *
	 * @return array
	 *
	 * @since 0.0.1
	 */
	function pod_definition( $pod_name, $type, $storage ) {
		if ( $type === 'pod' ) {
			$storage = 'table';
		}

		return array(
			'name' => $pod_name,
			'label' => ucfirst( $pod_name ),
			'description' => '',
			'options' =>
			array (
					'show_in_menu' => '1',
					'label_singular' => ucfirst( $pod_name ),
					'public' => '1',
					'show_ui' => '1',
					'supports_title' => '1',
					'supports_editor' => '1',
					'old_name' => 'location',
					'publicly_queryable' => '1',
					'exclude_from_search' => '0',
					'capability_type' => 'post',
					'capability_type_custom' => $pod_name,
					'capability_type_extra' => '1',
					'has_archive' => '0',
					'hierarchical' => '0',
					'rewrite' => '1',
					'rewrite_with_front' => '1',
					'rewrite_feeds' => '0',
					'rewrite_pages' => '1',
					'query_var' => '1',
					'can_export' => '1',
					'default_status' => 'draft',
					'supports_author' => '0',
					'supports_thumbnail' => '0',
					'supports_excerpt' => '0',
					'supports_trackbacks' => '0',
					'supports_custom_fields' => '0',
					'supports_comments' => '0',
					'supports_revisions' => '0',
					'supports_page_attributes' => '0',
					'supports_post_formats' => '0',
					'built_in_taxonomies_category' => '0',
					'built_in_taxonomies_link_category' => '0',
					'built_in_taxonomies_post_tag' => '0',
					'built_in_taxonomies_genre' => '0',
					'menu_position' => '0',
					'show_in_nav_menus' => '1',
					'show_in_admin_bar' => '1',
			),
			'type' => $type,
			'storage' => $storage,
			'object' => '',
			'alias' => '',
			);
	}

	/**
	 * Guarantees that before we create up an ACT or table-storage post type Pod that we have the right components active.
	 *
	 * NOTE: Does NOT guarantees they stay active
	 *
	 * @param string $type Type of Pod
	 * @param string $storage Storage type of Pod
	 *
	 * @since 0.0.1
	 */
	function activate_components( $type, $storage ) {
		if ( $type === 'pod' || $storage === 'table' ) {
			$component_settings = PodsInit::$components->settings;
			if ( $type === 'pod' ) {
				$component_settings['components']['advanced-content-types'] = array();
			}

			if ( $storage === 'table' ) {
				$component_settings['components']['table-storage'] = array();
			}

			update_option( 'pods_component_settings', json_encode( $component_settings ) );

		}

	}

	/**
	 * The field definitions for location Pod.
	 *
	 * @param string $pod_name The Pod's name.
	 * @param int $pod_id The Pod's id.
	 *
	 * @return array
	 *
	 * @since 0.0.1
	 */
	function fields( $pod_name, $pod_id ) {
		return array (
			'address_1' =>
				array (
					'name' => 'address_1',
					'label' => 'Address 1',
					'description' => '',
					'help' => '',
					'default' => NULL,
					'attributes' =>
						array (
						),
					'class' => '',
					'type' => 'text',
					'group' => 0,
					'grouped' => 0,
					'developer_mode' => false,
					'dependency' => false,
					'depends-on' =>
						array (
						),
					'excludes-on' =>
						array (
						),
					'options' =>
						array (
							'required' => '0',
							'text_allow_shortcode' => '0',
							'text_allow_html' => '0',
							'text_allowed_html_tags' => 'strong em a ul ol li b i',
							'text_max_length' => '255',
							'unique' => '0',
							'text_repeatable' => '0',
							'output_options' => NULL,
						),
					'weight' => 0,
					'pod_id' => $pod_id,
					'pod' =>$pod_name
				),
			'address_2' =>
				array (
					'name' => 'address_2',
					'label' => 'Address 2',
					'description' => '',
					'help' => '',
					'default' => NULL,
					'attributes' =>
						array (
						),
					'class' => '',
					'type' => 'text',
					'group' => 0,
					'grouped' => 0,
					'developer_mode' => false,
					'dependency' => false,
					'depends-on' =>
						array (
						),
					'excludes-on' =>
						array (
						),
					'options' =>
						array (
							'required' => '0',
							'text_allow_shortcode' => '0',
							'text_allow_html' => '0',
							'text_allowed_html_tags' => 'strong em a ul ol li b i',
							'text_max_length' => '255',
							'unique' => '0',
							'text_repeatable' => '0',
							'output_options' => NULL,
						),
					'weight' => 1,
					'pod_id' => $pod_id,
					'pod' => $pod_name
				),
			'city' =>
				array (
					'name' => 'city',
					'label' => 'City',
					'description' => '',
					'help' => '',
					'default' => NULL,
					'attributes' =>
						array (
						),
					'class' => '',
					'type' => 'text',
					'group' => 0,
					'grouped' => 0,
					'developer_mode' => false,
					'dependency' => false,
					'depends-on' =>
						array (
						),
					'excludes-on' =>
						array (
						),
					'options' =>
						array (
							'required' => '0',
							'text_allow_shortcode' => '0',
							'text_allow_html' => '0',
							'text_allowed_html_tags' => 'strong em a ul ol li b i',
							'text_max_length' => '255',
							'unique' => '0',
							'text_repeatable' => '0',
							'output_options' => NULL,
						),
					'weight' => 2,
					'pod_id' => $pod_id,
					'pod' =>$pod_name
				),
			'state_province' =>
				array (
					'name' => 'state_province',
					'label' => 'State Province',
					'description' => '',
					'help' => '',
					'default' => NULL,
					'attributes' =>
						array (
						),
					'class' => '',
					'type' => 'text',
					'group' => 0,
					'grouped' => 0,
					'developer_mode' => false,
					'dependency' => false,
					'depends-on' =>
						array (
						),
					'excludes-on' =>
						array (
						),
					'options' =>
						array (
							'required' => '0',
							'text_allow_shortcode' => '0',
							'text_allow_html' => '0',
							'text_allowed_html_tags' => 'strong em a ul ol li b i',
							'text_max_length' => '255',
							'unique' => '0',
							'text_repeatable' => '0',
							'output_options' => NULL,
						),
					'weight' => 3,
					'pod_id' => $pod_id,
					'pod' =>$pod_name
				),
			'postal_code' =>
				array (
					'name' => 'postal_code',
					'label' => 'Postal Code',
					'description' => '',
					'help' => '',
					'default' => NULL,
					'attributes' =>
						array (
						),
					'class' => '',
					'type' => 'text',
					'group' => 0,
					'grouped' => 0,
					'developer_mode' => false,
					'dependency' => false,
					'depends-on' =>
						array (
						),
					'excludes-on' =>
						array (
						),
					'options' =>
						array (
							'required' => '0',
							'text_allow_shortcode' => '0',
							'text_allow_html' => '0',
							'text_allowed_html_tags' => 'strong em a ul ol li b i',
							'text_max_length' => '255',
							'unique' => '0',
							'text_repeatable' => '0',
							'output_options' => NULL,
						),
					'weight' => 4,
					'pod_id' => $pod_id,
					'pod' =>$pod_name
				),
			'country' =>
				array (
					'name' => 'country',
					'label' => 'Country',
					'description' => '',
					'help' => '',
					'default' => NULL,
					'attributes' =>
						array (
						),
					'class' => '',
					'type' => 'text',
					'group' => 0,
					'grouped' => 0,
					'developer_mode' => false,
					'dependency' => false,
					'depends-on' =>
						array (
						),
					'excludes-on' =>
						array (
						),
					'options' =>
						array (
							'required' => '0',
							'text_allow_shortcode' => '0',
							'text_allow_html' => '0',
							'text_allowed_html_tags' => 'strong em a ul ol li b i',
							'text_max_length' => '255',
							'unique' => '0',
							'text_repeatable' => '0',
							'output_options' => NULL,
						),
					'weight' => 5,
					'pod_id' => $pod_id,
					'pod' =>$pod_name
				),
			'phone_number' =>
				array (
					'name' => 'phone_number',
					'label' => 'Phone Number',
					'description' => '',
					'help' => '',
					'default' => NULL,
					'attributes' =>
						array (
						),
					'class' => '',
					'type' => 'text',
					'group' => 0,
					'grouped' => 0,
					'developer_mode' => false,
					'dependency' => false,
					'depends-on' =>
						array (
						),
					'excludes-on' =>
						array (
						),
					'options' =>
						array (
							'required' => '0',
							'text_allow_shortcode' => '0',
							'text_allow_html' => '0',
							'text_allowed_html_tags' => 'strong em a ul ol li b i',
							'text_max_length' => '255',
							'unique' => '0',
							'text_repeatable' => '0',
							'output_options' => NULL,
						),
					'weight' => 6,
					'pod_id' => $pod_id,
					'pod' =>$pod_name
				),
			'geocode_lat' =>
				array (
					'name' => 'geocode_lat',
					'label' => 'GeoCoded Lattitude',
					'description' => '',
					'help' => '',
					'default' => NULL,
					'attributes' =>
						array (
						),
					'class' => '',
					'type' => 'text',
					'group' => 0,
					'grouped' => 0,
					'developer_mode' => false,
					'dependency' => false,
					'depends-on' =>
						array (
						),
					'excludes-on' =>
						array (
						),
					'options' =>
						array (
							'required' => '0',
							'text_allow_shortcode' => '0',
							'text_allow_html' => '0',
							'text_allowed_html_tags' => 'strong em a ul ol li b i',
							'text_max_length' => '255',
							'unique' => '0',
							'text_repeatable' => '0',
							'output_options' => NULL,
						),
					'weight' => 7,
					'pod_id' => $pod_id,
					'pod' => $pod_name
				),
			'geocode_long' =>
				array (
					'name' => 'geocode_long',
					'label' => 'GeoCoded Longitude',
					'description' => '',
					'help' => '',
					'default' => NULL,
					'attributes' =>
						array (
						),
					'class' => '',
					'type' => 'text',
					'group' => 0,
					'grouped' => 0,
					'developer_mode' => false,
					'dependency' => false,
					'depends-on' =>
						array (
						),
					'excludes-on' =>
						array (
						),
					'options' =>
						array (
							'required' => '0',
							'text_allow_shortcode' => '0',
							'text_allow_html' => '0',
							'text_allowed_html_tags' => 'strong em a ul ol li b i',
							'text_max_length' => '255',
							'unique' => '0',
							'text_repeatable' => '0',
							'output_options' => NULL,
						),
					'weight' => 8,
					'pod_id' => $pod_id,
					'pod' =>$pod_name
				),
			'delivery_range' =>
				array (
					'name' => 'delivery_range',
					'label' => 'delivery_range',
					'description' => '',
					'help' => '',
					'default' => NULL,
					'attributes' =>
						array (
						),
					'class' => '',
					'type' => 'text',
					'group' => 0,
					'grouped' => 0,
					'developer_mode' => false,
					'dependency' => false,
					'depends-on' =>
						array (
						),
					'excludes-on' =>
						array (
						),
					'options' =>
						array (
							'required' => '0',
							'text_allow_shortcode' => '0',
							'text_allow_html' => '0',
							'text_allowed_html_tags' => 'strong em a ul ol li b i',
							'text_max_length' => '255',
							'unique' => '0',
							'text_repeatable' => '0',
							'output_options' => NULL,
						),
					'weight' => 9,
					'pod_id' => $pod_id,
					'pod' =>$pod_name
				),
		);
	}

} //end hwp_dr_add_location_pod
