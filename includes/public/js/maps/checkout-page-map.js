// map.setOptions({
//    disableDefaultUI: true,
//  Overwrite map options
//  https://developers.google.com/maps/documentation/javascript/reference/map#MapOptions
// })

const geocoder   = new google.maps.Geocoder()
const infowindow = new google.maps.InfoWindow()

document.querySelector( "#lpac-find-location-btn" ).addEventListener(
	"click",
	() => {
    geocodeLatLng( geocoder, map, infowindow )
	}
)

function get_coordinates() {

	return new Promise(
		function(resolve, reject) {

			if (navigator.geolocation) {
				  navigator.geolocation.getCurrentPosition( resolve, reject )
			} else {
				// TODO add input fields so users can change this text
				alert( 'Geolocation is not possible on this web browser. Please switch to a different web browser to use our interactive map.' );
			}

		}
	).catch(
		function(error){

			console.log( 'Location Picker At Checkout Plugin: ' + error.message )

			if ( error.code === 1 ) {
				// TODO add input fields so users can change this text
				alert( "Something went wrong while trying to detect your location. Click on the location icon in the address bar and allow our website to detect your location. Please contact us if you need additional assistance." );
				return
			}

			alert( error.message );

		}
	)

}

/**
* <Global Settings>
*/
	// Globally scoped so that only one marker can be added to map.
	const marker = new google.maps.Marker(
		{
			draggable: true,
			map: map,
		}
	)

	var lpac_autofill_billing_fields = map_options.lpac_autofill_billing_fields
	/**
	 * </Global Settings>
	 */

	async function geocodeLatLng(geocoder, map, infowindow) {

		const position = await this.get_coordinates()

		if ( ! position ) {
			console.log( 'Location Picker At Checkout Plugin: Position object is empty. Navigator might be disabled or this site might be detected as insecure.' )
			return;
		}

		latitude  = position.coords.latitude
		longitude = position.coords.longitude

		const latlng = {
			lat: parseFloat( latitude ),
			lng: parseFloat( longitude ),
		}

		geocoder.geocode(
			{ location: latlng },
			(results, status) => {
				if (status === "OK") {
					if (results[0]) {

						map.setZoom( 16 )
						map.setCenter( { lat: latitude, lng: longitude } )

						marker.setPosition( latlng )

						let detected_address = results[0].formatted_address

						infowindow.setContent( detected_address )
						infowindow.open( map, marker )

						// document.querySelector('#current-address').innerHTML = detected_address
						console.log( results )
						lpac_fill_in_address_fields( results )
						// When Marker is Moved/Dragged
						google.maps.event.addListener(
							marker,
							'dragend',
						function (event) {

							const moved_to_lat = event.latLng.lat()
							const moved_to_lng = event.latLng.lng()

							const moved_to_latlng = {
								lat: parseFloat( moved_to_lat ),
								lng: parseFloat( moved_to_lng ),
							}

							geocoder.geocode(
								{ location: moved_to_latlng },
								(results, status) => {
                                // Debug
									console.log( results )
									if ( status === "OK" ) {
										let moved_to_address = results[0].formatted_address
										lpac_fill_in_address_fields( results )
										infowindow.setContent( moved_to_address )
									}
								}
							).then(
								function(resolved){
									// infowindow.close()
									document.querySelector( '#lpac_latitude' ).value  = moved_to_lat
									document.querySelector( '#lpac_longitude' ).value = moved_to_lng

									map.panTo( event.latLng )

								}
							).catch(
								function(error){

									console.log( error )
									// TODO Add error messages below map

									if ( error.code === 'OVER_QUERY_LIMIT' ) {
										error_msg = 'You are moving the map marker too quickly, use the zoom out button to move the marker across larger distances.'
										alert( error_msg )
										location.reload()
									}

								}
							)

						}
						)

					} else {
						window.alert( "No results found" )
					}
				} else {
            window.alert( "Geocoder failed due to: " + status )
				}

			  }
		)

		async function output_details(){

			document.querySelector( '#lpac_latitude' ).value  = latitude
			document.querySelector( '#lpac_longitude' ).value = longitude

		}

		output_details()

	}

	function lpac_fill_in_address_fields( results ){

		lpac_fill_in_shipping_country_region( results )
		lpac_fill_in_shipping_full_address( results )
		lpac_fill_in_shipping_town_city( results )
		lpac_fill_in_shipping_state_county( results )
		lpac_fill_in_shipping_zipcode( results )

		if ( lpac_autofill_billing_fields ) {
			lpac_fill_in_billing_country_region( results )
			lpac_fill_in_billing_full_address( results )
			lpac_fill_in_billing_town_city( results )
			lpac_fill_in_billing_state_county( results )
			lpac_fill_in_billing_zipcode( results )
		}

	}

	/*
	*  Get country from map.
	*/
	function lpac_get_country( results ){

		if ( ! results[0] ) {
			return;
		}

		var country         = ''
		const country_array = results[0].address_components.find( addr => addr.types[0] === "country" )

		if ( country_array ) {
			country = country_array.short_name
		}

		return country;
	}

	/*
	*  Get full formatted address
	*/
	function lpac_get_full_address( results ){

		if ( ! results[0] ) {
			return;
		}

		const full_address = results[0].formatted_address

		return full_address;
	}

	/*
	*  Get Town/City
	*/
	function lpac_get_town_city( results ){

		if ( ! results[0] ) {
			return;
		}

		var town_city          = ''
		const town_city_array  = results[0].address_components.find( addr => addr.types[0] === "locality" )
		const town_city_array2 = results[0].address_components.find( addr => addr.types[0] === "postal_town" )

		/*
		* Locality "locality" is used because its most commonly available.
		*/
		if ( town_city_array ) {
			town_city = town_city_array.long_name
		}

		/*
		* But we override Locality with the more standard "postal_town" field if it exists.
		*/
		if ( town_city_array2 ) {
			town_city = town_city_array2.long_name
		}

		return town_city;
	}

	/*
	*  Get State/County
	*/
	function lpac_get_state_county( results ){

		if ( ! results[0] ) {
			return;
		}

		var address_component = '';

		for (let address_component of results[0].address_components) {
			for ( type of address_component.types ) {
				if ( type === 'administrative_area_level_1' ) {
					return address_component;
				}
			}

		}

		  return address_component;

	}

	/*
	*  Get State/County
	*/
	function lpac_get_zip_code( results ){

		if ( ! results[0] ) {
			return;
		}

		var zipcode         = ''
		const zipcode_array = results[0].address_components.find( addr => addr.types[0] === "postal_code" )

		if ( zipcode_array ) {
			zipcode = zipcode_array.short_name
		}

		return zipcode;

	}

	/*
	*  Fill in shipping country field
	*/
	function lpac_fill_in_shipping_country_region( results ){

		const shipping_country = document.querySelector( '#shipping_country' );

		if ( typeof(shipping_country) === 'undefined' || shipping_country === null ) {
			return;
		}

		shipping_country.value = lpac_get_country( results );

		shipping_country.dispatchEvent( new Event( 'change', { 'bubbles': true } ) ) // ensure Select2 sees the change

	}

	/*
	*  Fill in billing country field
	*/
	function lpac_fill_in_billing_country_region( results ){

		const billing_country = document.querySelector( '#billing_country' );

		if ( typeof(billing_country) === 'undefined' || billing_country === null ) {
			return;
		}

		billing_country.value = lpac_get_country( results );
		billing_country.dispatchEvent( new Event( 'change', { 'bubbles': true } ) ) // ensure Select2 sees the change

	}

	/*
	*  Fill in shipping street address field
	*/
	function lpac_fill_in_shipping_full_address( results ){

		const full_shipping_address = document.querySelector( '#shipping_address_1' );

		if ( typeof(full_shipping_address) === 'undefined' || full_shipping_address === null ) {
			return;
		}

		full_shipping_address.value = lpac_get_full_address( results );

	}

	/*
	*  Fill in billing street address field
	*/
	function lpac_fill_in_billing_full_address( results ){

		const full_billing_address = document.querySelector( '#billing_address_1' );

		if (typeof(full_billing_address) === 'undefined' || full_billing_address === null) {
			return;
		}

		full_billing_address.value = lpac_get_full_address( results );

	}

	/*
	*  Fill in shipping Town/City field
	*/
	function lpac_fill_in_shipping_town_city( results ){

		const shipping_city = document.querySelector( '#shipping_city' );

		if ( typeof(shipping_city) === 'undefined' || shipping_city === null ) {
			return;
		}

		shipping_city.value = lpac_get_town_city( results );

	}

	/*
	*  Fill in billing Town/City field
	*/
	function lpac_fill_in_billing_town_city( results ){

		const billing_city = document.querySelector( '#billing_city' );

		if (typeof(billing_city) === 'undefined' && billing_city === null) {
			return;
		}

		billing_city.value = lpac_get_town_city( results );

	}

	/*
	*  Fill in shipping State/County field
	*/
	function lpac_fill_in_shipping_state_county( results ){

		if ( typeof(shipping_state_field) === 'undefined' || shipping_state_field === null ) {
			return;
		}

		/*
		* If we have values in our lpac_get_state_county() function
		*/
		if ( lpac_get_state_county( results ) ) {

			/*
			* This field changes based on the country.
			* For some countries WC shows a text input and others it shows a dropdown
			* We need to get the field everytime or risk JS not being able to set it.
			*/
			const shipping_state_field = document.querySelector( '#shipping_state' );

			if ( shipping_state_field.classList.contains( 'select2-hidden-accessible' ) ) {

				shipping_state_field.value = lpac_get_state_county( results ).short_name
				shipping_state_field.dispatchEvent( new Event( 'change', { 'bubbles': true } ) ) // ensure Select2 sees the change

			} else {

				shipping_state_field.value = lpac_get_state_county( results ).long_name

			}
		}

	}


	/*
	*  Fill in billing State/County field
	*/
	function lpac_fill_in_billing_state_county( results ){

		if ( typeof(billing_state_field) === 'undefined' || billing_state_field === null ) {
			return;
		}

		if ( lpac_get_state_county( results ) ) {

			/*
			* This field changes based on the country.
			* For some countries WC shows a text input and others it shows a dropdown
			* We need to get the field everytime or risk JS not being able to set it.
			*/
			const billing_state_field = document.querySelector( '#billing_state' );

			if ( billing_state_field.classList.contains( 'select2-hidden-accessible' ) ) {

				billing_state_field.value = lpac_get_state_county( results ).short_name
				billing_state_field.dispatchEvent( new Event( 'change', { 'bubbles': true } ) ) // ensure Select2 sees the change

			} else {

				billing_state_field.value = lpac_get_state_county( results ).long_name

			}

		}

	}

	/*
	*  Fill in shipping Zipcode field
	*/
	function lpac_fill_in_shipping_zipcode( results ){

		const shipping_zipcode = document.querySelector( '#shipping_postcode' );

		if ( typeof(shipping_zipcode) === 'undefined' || shipping_zipcode === null ) {
			return;
		}

		shipping_zipcode.value = lpac_get_zip_code( results );

	}

	/*
	*  Fill in billing Zipcode field
	*/
	function lpac_fill_in_billing_zipcode( results ){

		const billing_zipcode = document.querySelector( '#billing_postcode' );

		if ( typeof(billing_zipcode) === 'undefined' || billing_zipcode === null ) {
			return;
		}

		billing_zipcode.value = lpac_get_zip_code( results );
	}
