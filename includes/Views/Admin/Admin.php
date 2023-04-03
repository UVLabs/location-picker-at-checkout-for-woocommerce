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
namespace Lpac\Views\Admin;

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
		$settings[] = new \Lpac\Views\Admin\Admin_Settings();
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
		$latitude  = $order->get_meta( 'lpac_latitude' ) ?: $order->get_meta( '_lpac_latitude' );
		$latitude  = sanitize_text_field( $latitude );
		$longitude = $order->get_meta( 'lpac_longitude' ) ?: $order->get_meta( '_lpac_longitude' );
		$longitude = sanitize_text_field( $longitude );

		$store_origin_name = esc_html( $order->get_meta( '_lpac_order__origin_store_name' ) );

		$places_autocomplete_used = sanitize_text_field( $order->get_meta( '_lpac_places_autocomplete' ) );

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

		$places_autocomplete_used_text = '';
		if ( ! empty( $places_autocomplete_used ) ) {
			$places_autocomplete_used_text = sprintf( esc_html( __( 'It looks like this customer used the Places Autocomplete feature. The coordinates on the map might be an approximation. %s' ) ), "<a href='https://lpacwp.com/docs/getting-started/google-cloud-console/places-autocomplete-feature/#accuracy-of-places-autocomplete' target='_blank'> $learn_more </a>" );
		}

		$map_link = Functions::create_customer_directions_link( $latitude, $longitude );

		?>
		<div id='lpac-admin-order-meta'>
			<div class="lpac-admin-order-meta-location">
				<p><strong><?php echo esc_html( $customer_location_meta_text ); ?>:</strong></p>
				<p><a href="<?php echo esc_attr( $map_link ); ?>" target="_blank"><button class='btn button' style='cursor:pointer' type='button'><?php echo esc_html( $view_on_map_text ); ?></button></a></p>
				<p style="font-size: 12px"><?php echo esc_html( $places_autocomplete_used_text ); ?></p>
			</div>
		<?php

		if ( ! empty( $store_origin_name ) ) {
			$store_origin_name_meta_text = esc_html__( 'Selected Store', 'map-location-picker-at-checkout-for-woocommerce' );
			?>
		
		<p><strong><?php echo esc_html( $store_origin_name_meta_text ); ?>:</strong> <span style="color: green; text-decoration: underline"><?php echo esc_html( $store_origin_name ); ?></span></p>
		
		<?php } ?>

		</div>
		<?php
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

		add_meta_box( 'lpac_delivery_map_metabox', __( 'Location', 'map-location-picker-at-checkout-for-woocommerce' ), array( $this, 'output_custom_order_details_metabox' ), 'shop_order', 'normal', 'high' );
	}

	/**
	 * Outputs the HTML for the metabox
	 *
	 * @since 1.1.2
	 * @since 1.6.8 Fixed an issue where the address would not show if no shipping zones were created for website.
	 */
	public function output_custom_order_details_metabox() {

		$id = get_the_ID();

		$order = wc_get_order( $id );

		// Backwards compatibility, prior to v1.5.4 we stored location coords as private meta.
		$latitude  = (float) $order->get_meta( 'lpac_latitude' ) ?: (float) $order->get_meta( '_lpac_latitude' );
		$longitude = (float) $order->get_meta( 'lpac_longitude' ) ?: (float) $order->get_meta( '_lpac_longitude' );

		if ( $order->has_shipping_address() ) {
			$shipping_address_1 = $order->get_shipping_address_1();
			$shipping_address_2 = $order->get_shipping_address_2();
		} else { // Highly likely that the user didnt check the "Shipping to a different address?" option, so shipping fields wouldnt be present.
			$shipping_address_1 = $order->get_billing_address_1();
			$shipping_address_2 = $order->get_billing_address_2();
		}

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
			'lpac_map_default_latitude'                => $options['latitude'],
			'lpac_map_default_longitude'               => $options['longitude'],
			'lpac_map_zoom_level'                      => $options['zoom_level'],
			'lpac_map_clickable_icons'                 => $options['clickable_icons'] === 'yes' ? true : false,
			'lpac_map_background_color'                => $options['background_color'],
			'lpac_admin_order_screen_default_map_type' => $options['lpac_admin_order_screen_default_map_type'],
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

		?>
		<div id="wrap" style="display: block; text-align: center;">
			<div class="lpac-map" style="display: inline-block; border: 1px solid #eee; width: 100%; height:345px;"></div>
		</div>
		<?php
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

		?>
			<tr valign='top'>
			<th scope='row' class='titledesc'><?php echo esc_html( $name ); ?></th>
			<td>
				<button onclick="event.preventDefault(); <?php echo esc_attr( $script ); ?>" id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $class ); ?>" <?php echo esc_attr( $disabled ); ?>><?php echo esc_html( $text ); ?></button>
				<p class="description"><?php echo wp_kses_post( $description ); ?></p>
			</td>
			</tr>
		<?php

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
		?>
			<tr valign='top' id="<?php echo esc_attr( $id ); ?>">
			<th scope='row' class="titledesc <?php echo esc_attr( $class ); ?>" style='font-size: 18px'><?php echo esc_html( $name ); ?></th>
			<td>
				<hr/>
				<p class="description"><?php echo wp_kses_post( $description ); ?></p>
			</td>
			</tr>
		<?php
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

		?>
			<tr valign='top'>
			<th scope='row' class="titledesc"><?php echo esc_html( $name ); ?></th>
			<td>
				<div class="<?php echo esc_attr( $class ); ?>" style="<?php echo esc_attr( $css ); ?>"></div>
				<p class="description"><?php echo wp_kses_post( $description ); ?></p>
			</td>
			</tr>
		<?php
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

		?>
			<tr valign='top' class="<?php echo esc_attr( $row_class ); ?>">
			<th scope='row' class="titledesc"><?php echo esc_html( $name ); ?></th>
			<td>
				<p class="<?php echo esc_attr( $class ); ?>" style="<?php echo esc_attr( $css ); ?>" id="<?php echo esc_attr( $id ); ?>"><?php echo wp_kses_post( $text ); ?></p>
			</td>
			</tr>
		<?php
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
		$select_element_id             = $value['select_element_id'] ?? ''; // The name to set for our <select> element. The value passed here is saved as the key for the item that is selected from the dropdown options. This is later used to know which item to set as selected when rendering the list.
		$fields_disabled               = $value['fields_disabled'] ?? ''; // Whether we should disable all fields in this table

		$fields_disabled = disabled( $fields_disabled, true, false );

		$add                   = esc_html__( 'Add', 'map-location-picker-at-checkout-for-woocommerce' ) . ' ' . $entity_name;
		$delete_text           = esc_html__( 'Delete', 'map-location-picker-at-checkout-for-woocommerce' ) . ' ' . $entity_name;
		$default_dropdown_text = esc_html__( 'Please choose an option', 'map-location-picker-at-checkout-for-woocommerce' );

		$table_column_headings = '';
		foreach ( $table_columns as $id => $heading ) {
			$table_column_headings .= '<th>' . $heading['name'] . '</th>';
		}

		if ( is_array( $current_saved_settings_array ) && ! empty( $current_saved_settings_array ) ) {

			$repeater_items = '';
			foreach ( $current_saved_settings_array as $index => $current_saved_settings ) {
				$hold_inputs = ''; // clear the previously added inputs so we can concat the fresh pair

				$fields = array_keys( $current_saved_settings );

				foreach ( $fields as $field_name ) {

					if ( $id_field === $field_name ) { // TODO This is doesn't seem to actually run...
						continue;
					}

					$readonly = esc_attr( $table_columns[ $field_name ]['readonly'] ?? '' );
					$readonly = esc_attr( ( $readonly ) ? 'readonly' : '' );

					$required = esc_attr( $table_columns[ $field_name ]['required'] ?? '' );
					$required = esc_attr( ( $required ) ? 'required' : '' );

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
									$options .= "<option value='" . esc_attr( $item_id ) . "' selected>" . esc_html( $item_value ) . '</option>';
								} else {
									$options .= "<option value='" . esc_attr( $item_id ) . "'>" . esc_html( $item_value ) . '</option>';
								}
							}

							$hold_inputs .= "
									<td>
										<select name='" . esc_attr( $select_element_id ) . "' $fields_disabled>
											<option value=''>--" . esc_html( $default_dropdown_text ) . "--</option>
											$options
										</select>
									</td>
									";
							break;
						case 'checkbox':
							$checked      = (bool) $current_saved_settings['should_calculate_per_distance_unit_checkbox'] ?? '';
							$hold_inputs .= "<td><input type='checkbox' class='" . esc_attr( $field_name ) . "' name='" . esc_attr( $field_name ) . "' placeholder='" . esc_attr( $placeholder ) . "' $readonly $fields_disabled $required" . checked( $checked, true, false ) . '/></td>';
							break;
						default:
							$hold_inputs .= "<td><input type='text' class='" . esc_attr( $field_name ) . "' name='" . esc_attr( $field_name ) . "' value='" . esc_attr( $current_saved_settings[ $field_name ] ) . "' placeholder='" . esc_attr( $placeholder ) . "' $readonly $fields_disabled $required/></td>";
							break;
					}
				}

				$hold_inputs .= "<td><input data-repeater-delete type='button' value='" . esc_html( $delete_text ) . "' /></td>";

				$repeater_items .= '<tr data-repeater-item><div>' . $hold_inputs . '</tr></span>';
			}
		} else {
			$hold_inputs = ''; // clear the previously added inputs so we can concat the fresh pair

			foreach ( $table_columns as $key => $value ) {

				$type = $this->get_field_type( $key );

				$readonly    = ( $value['readonly'] ?? '' ) ? 'readonly' : '';
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
							$item_id    = $option_details[ $option_element_id ] ?? '';
							$item_value = $option_details[ $option_element_value ] ?? '';
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
					case 'checkbox':
						$hold_inputs .= "<td><input type='checkbox' name='$key' placeholder='$placeholder' $readonly $fields_disabled $required/></td>";
						break;
					default:
						$hold_inputs .= "<td><input type='text' name='$key' placeholder='$placeholder' $readonly $fields_disabled $required/></td>";
						break;
				}
			}

			$hold_inputs .= "<td><input data-repeater-delete type='button' value='$delete_text' /></td>";

			$repeater_items = '<tr data-repeater-item><div>' . $hold_inputs . '</div></tr>';
		}

		?>
				<tr valign='top' id="<?php echo esc_attr( $row_id ); ?>">
				<th scope='row' class="titledesc"><?php echo esc_html( $name ); ?></th>
				<td>
					<div class="repeater <?php echo esc_attr( $class ); ?>">	
						<table  class="" >
							<thead>
								<tr>
								<?php echo wp_kses_post( $table_column_headings ); ?>
								</tr>
							</thead>
							<tbody data-repeater-list="<?php echo esc_attr( $list_name ); ?>" >
								<?php echo ( $repeater_items ); //phpcs:ignore -- Individual enteries already sanitized ?>
							</tbody>
							<td><input data-repeater-create type="button" value="<?php echo esc_attr( $add ); ?>"/></td>
						</table>
					</div>
					<div><?php echo wp_kses_post( $description ); ?></div>
				</td>
				</tr>
		<?php
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
	 * Create a custom repeater element that can be used on the plugin's settings page.
	 *
	 * @since 1.6.8
	 * @param array $value
	 * @return void
	 */
	public function create_custom_wc_settings_upsell_banner( $value ) {

		/* translators: 1: HTML break element */
		$signup_text = sprintf( esc_html__( 'Custom Maps, Custom Marker Icons, Saved Addresses, More Visibility Rules, Cost by Region, Cost by Distance, Cost by Store Location, Multi-Store Distance Pricing, Export Order Locations & More. %s Get the most out of LPAC with the PRO version.', 'map-location-picker-at-checkout-for-woocommerce' ), '<br/><br/>' );
		/* translators: 1: Dashicons outbound link icon */
		$learn_more = sprintf( esc_html__( 'Learn More %s', 'map-location-picker-at-checkout-for-woocommerce' ), '<span style="text-decoration: none" class="dashicons dashicons-external"></span>' );

		?>
		<div class="lpac-banner-pro">
			<p style="font-size: 18px"><strong><?php echo $signup_text //phpcs:ignore -- Already escaped above.; ?></strong></p>
			<br/>
			<p><a class="lpac-button" href="https://lpacwp.com/pricing?utm_source=banner&utm_medium=lpacdashboard&utm_campaign=proupsell" target="_blank"><?php echo $learn_more; //phpcs:ignore -- Already escaped above ?></a></p>
		</div>
		<?php

	}

	public function create_custom_wc_settings_image( $value ) {

		// $name      = $value['name'];
		$id        = $value['id'];
		$row_class = $value['row_class'] ?? '';
		$class     = $value['class'];
		$desc      = $value['desc'];
		$src       = $value['src'];
		$height    = $value['height'] ?? 'auto';
		$url       = $value['url'] ?? '#';

		?>
				<tr valign='top' class="<?php echo esc_attr( $row_class ); ?>">
				<!-- <th scope='row' class="titledesc"></th> -->
				<td>
					<a href='<?php echo esc_attr( $url ); ?>' target='_blank'><img src="<?php echo esc_attr( $src ); ?>" class="<?php echo esc_attr( $class ); ?>" id="<?php echo esc_attr( $id ); ?>" height="<?php echo esc_attr( $height ); ?>" /></a>
					<p style="font-size: 18px; font-weight: 700; text-align: left; margin-top: 10px"><?php echo wp_kses_post( $desc ); ?></p>
				</td>
				</tr>
		<?php
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

		$map_link = Functions::create_customer_directions_link( $latitude, $longitude );

		$text = esc_html__( 'View', 'map-location-picker-at-checkout-for-woocommerce' );
		?>
		<p><a href='<?php echo esc_attr( $map_link ); ?>' target='_blank'><button class='btn button' style='cursor:pointer' type='button'><?php echo esc_html( $text ); ?></button></a></p>
		<?php
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

		?>
			<p><?php echo esc_html( $store_name ); ?></p>
		<?php
	}

}
