<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://uriahsvictor.com
 * @since      1.0.0
 *
 * @package    Lpac
 */
namespace Lpac\Views;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Lpac\Helpers\Functions;

class Admin {

	/**
	 * Add our WooCommerce settings tab
	 *
	 * @param mixed $settings Array of existing WooCommerce settings tab
	 * @return mixed
	 */
	public function lpac_add_settings_tab( $settings ) {
		$settings[] = new \Lpac\Views\Admin_Settings;
		return $settings;
	}

	/**
	 * Displays the view on map button on the admin order page.
	 *
	 * @since    1.0.0
	 * @param object $order The order object.
	 */
	public function lpac_display_lpac_admin_order_meta( $order ) {

		// Backwards compatibility, previously(prior to v1.5.4) we stored location coords as private meta.
		$latitude          = get_post_meta( $order->get_id(), 'lpac_latitude', true ) ?: get_post_meta( $order->get_id(), '_lpac_latitude', true );
		$longitude         = get_post_meta( $order->get_id(), 'lpac_longitude', true ) ?: get_post_meta( $order->get_id(), '_lpac_longitude', true );
		$store_origin_name = get_post_meta( $order->get_id(), '_lpac_order__origin_store_name', true );

		$places_autocomplete_used = get_post_meta( $order->get_id(), '_lpac_places_autocomplete', true );

		/* translators: 1: Dashicons outbound link icon*/
		$learn_more = sprintf( __( 'Learn More %s', 'map-location-picker-at-checkout-for-woocommerce' ), '<span style="text-decoration: none" class="dashicons dashicons-external"></span>' );

		/**
		 * If we have no values for these options bail.
		 */
		if ( empty( $latitude ) || empty( $longitude ) ) {
			return;
		}

		$customer_location_meta_text = esc_html__( 'Customer Location', 'map-location-picker-at-checkout-for-woocommerce' );
		$view_on_map_text            = esc_html__( 'View', 'map-location-picker-at-checkout-for-woocommerce' );
		$store_origin_name_meta_text = esc_html__( 'Selected Store', 'map-location-picker-at-checkout-for-woocommerce' );

		$places_autocomplete_used_text = '';
		if ( ! empty( $places_autocomplete_used ) ) {
			$places_autocomplete_used_text = sprintf( esc_html( __( 'It looks like this customer used the Places Autocomplete feature. The coordinates on the map might be an approximation. %s' ) ), "<a href='https://lpacwp.com/docs/getting-started/google-cloud-console/places-autocomplete-feature/#accuracy-of-places-autocomplete' target='_blank'> $learn_more </a>" );
		}

		$map_link = apply_filters( 'lpac_map_provider', "https://www.google.com/maps/search/?api=1&query=${latitude},${longitude}", $latitude, $longitude );

		$markup = <<<HTML
		<p><strong>$customer_location_meta_text:</strong></p>
		<p><a href="$map_link" target="_blank"><button class='btn button' style='cursor:pointer' type='button'>$view_on_map_text</button></a></p>
		<p style="font-size: 12px">$places_autocomplete_used_text</p>
HTML;

		if ( ! empty( $store_origin_name ) ) {
			$markup .= <<<HTML
		<p><strong>$store_origin_name_meta_text:</strong> <span style="color: green; text-decoration: underline">$store_origin_name</span></p>
HTML;
		}

		echo $markup;
	}

	/**
	 * Create the metabox for holding the map view in admin order details.
	 *
	 * @since    1.1.2
	 */
	public function lpac_create_custom_order_details_metabox() {

		// Backwards compatibility, previously we stored location coords as private meta.
		$latitude  = get_post_meta( get_the_ID(), 'lpac_latitude', true ) ?: get_post_meta( get_the_ID(), '_lpac_latitude', true );
		$longitude = get_post_meta( get_the_ID(), 'lpac_longitude', true ) ?: get_post_meta( get_the_ID(), '_lpac_longitude', true );

		/**
		 * If we have no values for these options bail.
		 */
		if ( empty( $latitude ) || empty( $longitude ) ) {
			return;
		}

		add_meta_box( 'lpac_delivery_map_metabox', __( 'Location', 'map-location-picker-at-checkout-for-woocommerce' ), array( $this, 'lpac_output_custom_order_details_metabox' ), 'shop_order', 'normal', 'high' );
	}

	/**
	 * Outputs the HTML for the metabox
	 *
	 * @since    1.1.2
	 */
	public function lpac_output_custom_order_details_metabox() {

		$id = get_the_ID();

		// Backwards compatibility, previously we stored location coords as private meta.
		$latitude  = (float) get_post_meta( $id, 'lpac_latitude', true ) ?: (float) get_post_meta( $id, '_lpac_latitude', true );
		$longitude = (float) get_post_meta( $id, 'lpac_longitude', true ) ?: (float) get_post_meta( $id, '_lpac_longitude', true );

		$shipping_address_1 = get_post_meta( $id, '_shipping_address_1', true );
		$shipping_address_2 = get_post_meta( $id, '_shipping_address_2', true );

		/**
		 * If we have no values for these options bail.
		 */
		if ( empty( $latitude ) || empty( $longitude ) ) {
			return;
		}

		$order_location_details = array(
			'latitude'           => $latitude,
			'longitude'          => $longitude,
			'shipping_address_1' => $shipping_address_1,
			'shipping_address_2' => $shipping_address_2,
		);

		$options = Functions::set_map_options();

		$data = array(
			'lpac_map_default_latitude'  => $options['latitude'],
			'lpac_map_default_longitude' => $options['longitude'],
			'lpac_map_zoom_level'        => $options['zoom_level'],
			'lpac_map_clickable_icons'   => $options['clickable_icons'] === 'yes' ? true : false,
			'lpac_map_background_color'  => $options['background_color'],
		);

		$order_location_details = json_encode( $order_location_details );
		$map_options            = json_encode( $data );

		$global_variables = <<<JAVASCRIPT
	// Lpac Order Location Details
	var locationDetails = $order_location_details;
	// Lpac Map Settings
	var mapOptions = $map_options;
JAVASCRIPT;

		// Expose JS variables for usage
		wp_add_inline_script( LPAC_PLUGIN_NAME . '-base-map', $global_variables, 'before' );

		$map_container = <<<HTML
			<div id="wrap" style="display: block; text-align: center;">
			<div class="lpac-map" style="display: inline-block; padding 10; border: 1px solid #eee; width: 100%; height:345px;"></div>
			</div>
HTML;

		echo $map_container;

	}

	/**
	 * Create a custom button that can be used on the plugin's settings page.
	 *
	 * @param array $value
	 * @return void
	 */
	public function create_custom_wc_settings_button( $value ) {

		$class       = $value['class'];
		$id          = $value['id'];
		$text        = $value['value'];
		$name        = $value['name'];
		$description = $value['desc'];
		$link        = $value['link'] ?? '';

		$custom_attributes = $value['custom_attributes'] ?? '';
		$disabled          = $custom_attributes['disabled'] ?? '';
		$script            = $link ? "window.open('$link')" : ''; // Adds button location link js if a 'link' value present on the field.

		$markup = <<<HTML
				<tr valign='top'>
				<th scope='row' class='titledesc'>$name</th>
				<td>
					<button onclick="event.preventDefault(); $script" id="$id" class="$class" $disabled>$text</button>
					<p class="description">$description</p>
				</td>
				</tr>
HTML;
		echo $markup;

	}

	/**
	 * Create a custom hr element that can be used on the plugin's settings page.
	 *
	 * @param array $value
	 * @return void
	 */
	public function create_custom_wc_settings_hr( $value ) {

		$class       = $value['class'];
		$name        = $value['name'];
		$description = $value['desc'];
		$id          = $value['id'] ?? '';

		$markup = <<<HTML
				<tr valign='top' id="$id">
				<th scope='row' class="titledesc $class" style='font-size: 18px'>$name</th>
				<td>
					<hr/>
					<p class="description">$description</p>
				</td>
				</tr>
HTML;
		echo $markup;

	}

	/**
	 * Create a custom hr element that can be used on the plugin's settings page.
	 *
	 * @param array $value
	 * @return void
	 */
	public function create_custom_wc_settings_div( $value ) {

		$class       = $value['class'];
		$name        = $value['name'];
		$description = $value['desc'];
		$css         = $value['css'];

		$markup = <<<HTML
				<tr valign='top'>
				<th scope='row' class="titledesc">$name</th>
				<td>
					<div class="$class" style="$css"></div>
					<!-- <hr/> -->
					<p class="description">$description</p>
				</td>
				</tr>
HTML;
		echo $markup;

	}

	/**
	 * Create a custom info p element that can be used on the plugin's settings page.
	 *
	 * @param array $value
	 * @return void
	 */
	public function create_custom_wc_settings_info_text( $value ) {

		$name      = $value['name'];
		$id        = $value['id'];
		$row_class = $value['row_class'] ?? '';
		$class     = $value['class'];
		$text      = $value['text'];
		$css       = $value['css'];

		$markup = <<<HTML
				<tr valign='top' class="$row_class">
				<th scope='row' class="titledesc">$name</th>
				<td>
					<p class="$class" style="$css" id="$id">$text</p>
				</td>
				</tr>
HTML;
		echo $markup;
	}

	/**
	 * Create a custom repeater element that can be used on the plugin's settings page.
	 *
	 * @since 1.6.0
	 * @param array $value
	 * @return void
	 */
	public function create_custom_wc_settings_repeater( $value ) {

		$class                         = $value['class'];
		$name                          = $value['name'];
		$description                   = $value['desc'] ?? '';
		$css                           = $value['css'] ?? '';
		$current_saved_settings_array  = $value['current_saved_settings'] ?? '';
		$table_columns                 = $value['table_columns'];
		$list_name                     = $value['id'];
		$row_id                        = $value['row_id'] ?? ''; // This is simply a CSS id for the repeater row so that the whole thing can be hidden in JS
		$id_field                      = $value['id_field'] ?? ''; // The field that will be used as the unique identifier for the entry
		$entity_name                   = $value['entity_name']; // The name of the entity we are allowing the user to create
		$select_field_dropdown_options = $value['select_field_dropdown_options'] ?? ''; // The options to populate the select field dropdown with
		$option_element_id             = $value['option_element_id'] ?? '';  // The array element we want to set for the HTML option 'id'
		$option_element_value          = $value['option_element_value'] ?? ''; // The array element we want to set for the HTML option 'value'
		$select_element_id             = $value['select_element_id'] ?? ''; // The name to set for our <select> element. The value passed here is saved as the key for the item that is selected from the dropdown options.
		$fields_disabled               = $value['fields_disabled'] ?? ''; // Whether we should disable all fields in this table

		$fields_disabled = disabled( $fields_disabled, true, false );

		$add                   = esc_html__( 'Add', 'map-location-picker-at-checkout-for-woocommerce' ) . ' ' . $entity_name;
		$delete_text           = esc_html__( 'Delete', 'map-location-picker-at-checkout-for-woocommerce' ) . ' ' . $entity_name;
		$default_dropdown_text = esc_html__( 'Please choose an option', 'map-location-picker-at-checkout-for-woocommerce' );

		$table_column_headings = '';
		foreach ( $table_columns as $id => $heading ) {
			$table_column_headings .= '<th>' . $heading['name'] . '</th>';
		}

		if ( ! empty( $current_saved_settings_array ) && is_array( $current_saved_settings_array ) ) {

			$repeater_items = '';
			foreach ( $current_saved_settings_array as $index => $current_saved_settings ) {
				$hold_inputs = ''; // clear the previously added inputs so we can concat the fresh pair

				$fields = array_keys( $current_saved_settings );

				foreach ( $fields as $field_name ) {

					if ( $id_field === $field_name ) {
						continue;
					}

					$readonly = $table_columns[ $field_name ]['readonly'] ?? '';
					$readonly = ( $readonly ) ? 'readonly' : '';

					$required = $table_columns[ $field_name ]['required'] ?? '';
					$required = ( $required ) ? 'required' : '';

					$placeholder = $table_columns[ $field_name ]['placeholder'] ?? '';

					$type = $this->get_field_type( $field_name );

					switch ( $type ) {
						case 'select':
							if ( empty( $select_field_dropdown_options ) || ! is_array( $select_field_dropdown_options ) ) {
								break;
							}

							$options = '';

							foreach ( $select_field_dropdown_options as $key => $option_details ) {
								$item_id    = $option_details[ $option_element_id ];
								$item_value = $option_details[ $option_element_value ];

								if ( $current_saved_settings[ $select_element_id ] === $item_id ) {
									$options .= "<option value='$item_id' selected>$item_value</option>";
								} else {
									$options .= "<option value='$item_id'>$item_value</option>";
								}
							}

							$hold_inputs .= "
									<td>
										<select name='$select_element_id' $fields_disabled>
											<option value=''>--$default_dropdown_text--</option>
											$options
										</select>
									</td>
									";
							break;

						default:
							$hold_inputs .= "<td><input type='text' class='$field_name' name='$field_name' value='$current_saved_settings[$field_name]' placeholder='$placeholder' $readonly $fields_disabled $required/></td>";
							break;
					}
				}

				$hold_inputs .= "<td><input data-repeater-delete type='button' value='$delete_text' /></td>";

				$repeater_items .= '<tr data-repeater-item><div>' . $hold_inputs . '</tr></span>';
			}
		} else {
			$hold_inputs = ''; // clear the previously added inputs so we can concat the fresh pair

			foreach ( $table_columns as $key => $value ) {

				$type = $this->get_field_type( $key );

				$readonly    = ( $value['readonly'] ) ? 'readonly' : '';
				$placeholder = $value['placeholder'] ?? '';

				$required = $value['required'] ?? '';
				$required = ( $required ) ? 'required' : '';

				switch ( $type ) {
					case 'select':
						if ( empty( $select_field_dropdown_options ) || ! is_array( $select_field_dropdown_options ) ) {
							break;
						}

						$options = '';

						foreach ( $select_field_dropdown_options as $index => $option_details ) {
							$item_id    = $option_details[ $option_element_id ];
							$item_value = $option_details[ $option_element_value ];
							$options   .= "<option value='$item_id'>$item_value</option>";
						}

						$hold_inputs .= "
						<td>
							<select name='$select_element_id' $fields_disabled>
								<option value=''>--$default_dropdown_text--</option>
								$options
							</select>
						</td>";
						break;

					default:
						$hold_inputs .= "<td><input type='text' name='$key' placeholder='$placeholder' $readonly $fields_disabled $required/></td>";
						break;
				}
			}

			$hold_inputs .= "<td><input data-repeater-delete type='button' value='$delete_text' /></td>";

			$repeater_items = '<tr data-repeater-item><div>' . $hold_inputs . '</div></tr>';
		}

		$markup = <<<HTML
				<tr valign='top' id="$row_id">
				<th scope='row' class="titledesc">$name</th>
				<td>
					<div class="repeater $class">	
						<table  class="" >
							<thead>
								<tr>
								$table_column_headings
								</tr>
							</thead>
							<tbody data-repeater-list="$list_name" >
								$repeater_items
							</tbody>
							<td><input data-repeater-create type="button" value="$add"/></td>
						</table>
					</div>
					<div>$description</div>
				</td>
				</tr>
HTML;

		echo $markup;
	}

	/**
	 * Get a field type based on it's name.
	 *
	 * @since 1.6.0
	 * @param string $field_name
	 * @return string
	 */
	private function get_field_type( string $field_name ) : string {
		$type = explode( '_', $field_name );
		$type = end( $type );

		return $type;
	}

	/**
	 * Add our custom column to the list of columns.
	 *
	 * @param array $columns
	 * @since v1.6.6
	 * @return mixed
	 */
	public function add_map_btn_admin_list_column( $columns ) {
		$columns['lpac_location'] = __( 'Location', 'map-location-picker-at-checkout-for-woocommerce' );
		return $columns;
	}

	/**
	 * Add our content to our custom column.
	 *
	 * @param mixed $column
	 * @since v1.6.6
	 * @return void
	 */
	public function add_map_btn_admin_list_column_content( $column ) {

		global $post;

		if ( $column !== 'lpac_location' ) {
			return;
		}

		$order = wc_get_order( $post->ID );

		$latitude  = $order->get_meta( 'lpac_latitude' );
		$longitude = $order->get_meta( 'lpac_longitude' );

		if ( empty( $latitude ) || empty( $longitude ) ) {
			return;
		}

		$map_link = apply_filters( 'lpac_map_provider', "https://www.google.com/maps/search/?api=1&query=${latitude},${longitude}", $latitude, $longitude );

		$text = esc_html__( 'View', 'map-location-picker-at-checkout-for-woocommerce' );
		echo "
			<p><a href='$map_link' target='_blank'><button class='btn button' style='cursor:pointer' type='button'>$text</button></a></p>
		";
	}

	/**
	 * Add our custom column to the list of columns.
	 *
	 * @param array $columns
	 * @since v1.6.6
	 * @return mixed
	 */
	public function add_store_location_admin_list_column( $columns ) {
		$columns['lpac_selected_store'] = __( 'Store', 'map-location-picker-at-checkout-for-woocommerce' );
		return $columns;
	}

	/**
	 * Add our content to our custom column.
	 *
	 * @param mixed $column
	 * @since v1.6.6
	 * @return void
	 */
	public function add_store_location_admin_list_column_content( $column ) {

		global $post;

		if ( $column !== 'lpac_selected_store' ) {
			return;
		}

		$order = wc_get_order( $post->ID );

		$store_name = $order->get_meta( '_lpac_order__origin_store_name' );

		if ( empty( $store_name ) ) {
			return;
		}

		echo "
			<p>$store_name</p>
		";
	}

}
