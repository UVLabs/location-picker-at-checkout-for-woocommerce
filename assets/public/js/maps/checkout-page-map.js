// map.setOptions({
//    disableDefaultUI: true,
//  Overwrite map options
//  https://developers.google.com/maps/documentation/javascript/reference/map#MapOptions
// })

/* Get our global map variables */
const map = window.lpac_map;
const marker = window.lpac_marker;
const infowindow = window.lpac_infowindow;

const geocoder = new google.maps.Geocoder();

const find_location_btn = document.querySelector("#lpac-find-location-btn");
const places_autocomplete_used = document.querySelector('#lpac_places_autocomplete');

if (typeof (find_location_btn) !== 'undefined' && find_location_btn !== null) {
	find_location_btn.addEventListener(
		"click",
		() => {
			lpac_bootstrap_map_functionality(geocoder, map, infowindow)
		}
	)
} else {
	console.log('LPAC: Detect location button not present, skipping...')
}

/**
 * Removes the plus code from an address if the option is turned on in the plugin's settings.
 */
function lpacRemovePlusCode( address ){

	if( ! mapOptions.lpac_remove_address_plus_code ){
		return address;
	}

	const firstBlock = address.split(' ', 1);

	if( firstBlock[0].includes('+') ){
		address = address.replace( firstBlock[0], '' ).trim();
	}

	return address;

}

/**
 * Get Lat and Long cords from browser navigator.
 */
function get_navigator_coordinates() {

	return new Promise(
		function (resolve, reject) {

			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(resolve, reject)
			} else {
				// TODO add input fields so users can change this text
				alert('Geolocation is not possible on this web browser. Please switch to a different web browser to use our interactive map.');
			}

		}
	).catch(
		function (error) {

			console.log('Location Picker At Checkout Plugin: ' + error.message)

			if (error.code === 1) {
				// TODO add input fields so users can change this text
				alert("Something went wrong while trying to detect your location. Click on the location icon in the address bar and allow our website to detect your location. Please contact us if you need additional assistance.");
				return
			}

			alert(error.message);
		}
	)

}

/** 
*  Bootstrap the functionality of the map and marker.
*/
async function lpac_bootstrap_map_functionality(geocoder, map, infowindow) {

	const position = await get_navigator_coordinates();

	if (!position) {
		console.log('Location Picker At Checkout Plugin: Position object is empty. Navigator might be disabled or this site might be detected as insecure.')
		return;
	}

	let latitude = position.coords.latitude
	let longitude = position.coords.longitude

	const latlng = {
		lat: parseFloat(latitude),
		lng: parseFloat(longitude),
	}

	/**
	 * Place our initial map marker.
	 */
	lpac_setup_initial_map_marker_position(latlng)

	/**
	* Fill in latitude and longitude fields.
	*/
	lpac_fill_in_latlng(latlng);

	places_autocomplete_used.value = 0;

}

/**
 * Function getting address details from latitude and longitudes.
 */
async function lpac_geocode_coordinates(latlng) {

	var address_array = '';

	await geocoder.geocode({ location: latlng }, (results, status) => {

		if (status === "OK") {

			if (results[ 0 ]) {

				address_array = results;

			} else {
				window.alert("No results found")
				return;
			}
		} else {
			console.log("Geocoder failed due to: " + status)
			return
		}

	}

	).then(
		function (resolved) {

			map.panTo(latlng);

		}
	).catch(
		function (error) {

			console.log(error)
			// TODO Add error messages below map

			if (error.code === 'OVER_QUERY_LIMIT') {
				// TODO Localize this string
				error_msg = 'Slow down, you are moving too quickly, use the zoom out button to move the marker across larger distances.';
				alert(error_msg)
				location.reload()
			}

		}
	)

	return address_array;

}

/**
* Setup the intial marker location.
*/
async function lpac_setup_initial_map_marker_position(latlng) {

	const results = await lpac_geocode_coordinates(latlng);

	if (!results[ 0 ]) {
		return
	}

	map.setZoom(16);
	map.setCenter(latlng);

	marker.setPosition(latlng);

	let detected_address = results[ 0 ].formatted_address;

	detected_address = lpacRemovePlusCode( detected_address );
	
	infowindow.setContent(detected_address);
	infowindow.open(map, marker);

	lpac_fill_in_address_fields(results, latlng);
	lpac_marker_listen_to_drag();
	lpac_map_listen_to_clicks();

}

/** 
*  Handle clicking of map so marker, fields and coordinates inputs get filled in.
*/
function lpac_map_listen_to_clicks() {

	map.addListener('click',
		async function (event) {

			const results = await lpac_geocode_coordinates(event.latLng);

			if (!results[ 0 ]) {
				console.log('LPAC: Results not as expected. See lpac_map_listen_to_clicks()')
				return;
			}

			const lat = event.latLng.lat()
			const lng = event.latLng.lng()

			const latLng = {
				lat: parseFloat(lat),
				lng: parseFloat(lng),
			}

			lpac_fill_in_address_fields(results, latLng);

			marker.setPosition(event.latLng)

			let detected_address = results[ 0 ].formatted_address;

			detected_address = lpacRemovePlusCode(detected_address);

			infowindow.setContent(detected_address);
			infowindow.open(map, marker);

			places_autocomplete_used.value = 0;
		});

}
window.lpac_map_listen_to_clicks = lpac_map_listen_to_clicks;

/** 
*  Handle dragging of marker so fields and coordinates inputs get filled in.
*/
function lpac_marker_listen_to_drag() {

	google.maps.event.addListener(
		marker,
		'dragend',
		async function (event) {

			const moved_to_lat = event.latLng.lat()
			const moved_to_lng = event.latLng.lng()

			const moved_to_latlng = {
				lat: parseFloat(moved_to_lat),
				lng: parseFloat(moved_to_lng),
			}

			let results = await lpac_geocode_coordinates(moved_to_latlng);

			if (!results[ 0 ]) {
				console.log('Results not as expected. See lpac_marker_listen_to_drag()')
				return;
			}

			let moved_to_address = results[ 0 ].formatted_address;

			moved_to_address = lpacRemovePlusCode(moved_to_address);

			infowindow.setContent(moved_to_address);

			lpac_fill_in_address_fields(results, moved_to_latlng);
			places_autocomplete_used.value = 0;

		}

	)

}
window.lpac_marker_listen_to_drag = lpac_marker_listen_to_drag;

/**
* Function responsible filling in the latitude and longitude fields.
*/
function lpac_fill_in_latlng(latlng) {

	if (!latlng.lat || !latlng.lng) {
		console.log('Location Picker At Checkout Plugin: Empty latlng. See lpac_fill_in_latlng()');
		return;
	}

	let latitude = document.querySelector('#lpac_latitude');
	let longitude = document.querySelector('#lpac_longitude');

	if (typeof (latitude) === 'undefined' || latitude === null) {
		console.log('LPAC: Can\'t find latitude and longitude input areas. Can\'t insert location coordinates.');
	}

	if (typeof (longitude) === 'undefined' || longitude === null) {
		console.log('LPAC: Can\'t find latitude and longitude input areas. Can\'t insert location coordinates.');
	}

	latitude.value = latlng.lat
	longitude.value = latlng.lng

}

/**
* Function responsible for ochestrating the address filling methods.
*/
function lpac_fill_in_address_fields(results, latLng = '') {

	lpac_fill_in_latlng(latLng)

	lpac_fill_in_shipping_fields( results );

	if (typeof (mapOptions) === 'undefined' || mapOptions === null) {
		console.log('LPAC: mapOptions object not present, skipping...')
		return;
	}

	const lpac_autofill_billing_fields = mapOptions.lpac_autofill_billing_fields

	if (lpac_autofill_billing_fields) {
		lpac_fill_in_billing_fields( results );
	}

}

/**
 * Fill in all shipping fields.
 * 
 * @param {array} results 
 */
function lpac_fill_in_shipping_fields( results ){
	lpac_fill_in_shipping_country_region(results)
	lpac_fill_in_shipping_full_address(results)
	lpac_fill_in_shipping_town_city(results)
	lpac_fill_in_shipping_state_county(results)
	lpac_fill_in_shipping_zipcode(results)
}

/**
 * Fill in all billing fields.
 * 
 * @param {array} results 
 */
function lpac_fill_in_billing_fields( results ){
	lpac_fill_in_billing_country_region(results)
	lpac_fill_in_billing_full_address(results)
	lpac_fill_in_billing_town_city(results)
	lpac_fill_in_billing_state_county(results)
	lpac_fill_in_billing_zipcode(results)
}

/*
*  Get country from map.
*/
function lpac_get_country(results) {

	if (!results[ 0 ]) {
		return;
	}

	var country = ''
	const country_array = results[ 0 ].address_components.find(addr => addr.types[ 0 ] === "country")

	if (country_array) {
		country = country_array.short_name
	}

	return country;
}

/*
*  Get full formatted address
*/
function lpac_get_full_address(results) {

	if (!results[ 0 ]) {
		return;
	}

	let full_address = results[ 0 ].formatted_address;

	full_address = lpacRemovePlusCode(full_address);

	return full_address;
}

/*
*  Get Town/City
*/
function lpac_get_town_city(results) {

	if (!results[ 0 ]) {
		return;
	}

	var town_city = ''
	const town_city_array = results[ 0 ].address_components.find(addr => addr.types[ 0 ] === "locality")
	const town_city_array2 = results[ 0 ].address_components.find(addr => addr.types[ 0 ] === "postal_town")

	/*
	* Locality "locality" is used because its most commonly available.
	*/
	if (town_city_array) {
		town_city = town_city_array.long_name
	}

	/*
	* But we override Locality with the more standard "postal_town" field if it exists.
	*/
	if (town_city_array2) {
		town_city = town_city_array2.long_name
	}

	return town_city;
}

/*
*  Get State/County
*/
function lpac_get_state_county(results) {

	if (!results[ 0 ]) {
		return;
	}

	let address_component = '';

	for (let address_component of results[ 0 ].address_components) {
		for (type of address_component.types) {
			if (type === 'administrative_area_level_1') {
				return address_component;
			}
		}

	}

	return address_component;

}

/*
*  Get State/County
*/
function lpac_get_zip_code(results) {

	if (!results[ 0 ]) {
		return;
	}

	var zipcode = ''
	const zipcode_array = results[ 0 ].address_components.find(addr => addr.types[ 0 ] === "postal_code")

	if (zipcode_array) {
		zipcode = zipcode_array.short_name
	}

	return zipcode;

}

/*
*  Fill in shipping country field
*/
function lpac_fill_in_shipping_country_region(results) {

	const shipping_country = document.querySelector('#shipping_country');

	if (typeof (shipping_country) === 'undefined' || shipping_country === null) {
		return;
	}

	shipping_country.value = lpac_get_country(results);

	shipping_country.dispatchEvent(new Event('change', { 'bubbles': true })) // ensure Select2 sees the change

}

/*
*  Fill in billing country field
*/
function lpac_fill_in_billing_country_region(results) {

	const billing_country = document.querySelector('#billing_country');

	if (typeof (billing_country) === 'undefined' || billing_country === null) {
		return;
	}

	billing_country.value = lpac_get_country(results);
	billing_country.dispatchEvent(new Event('change', { 'bubbles': true })) // ensure Select2 sees the change

}

/*
*  Fill in shipping street address field
*/
function lpac_fill_in_shipping_full_address(results) {

	const full_shipping_address = document.querySelector('#shipping_address_1');

	if (typeof (full_shipping_address) === 'undefined' || full_shipping_address === null) {
		return;
	}

	full_shipping_address.value = lpac_get_full_address(results);

}

/*
*  Fill in billing street address field
*/
function lpac_fill_in_billing_full_address(results) {

	const full_billing_address = document.querySelector('#billing_address_1');

	if (typeof (full_billing_address) === 'undefined' || full_billing_address === null) {
		return;
	}

	full_billing_address.value = lpac_get_full_address(results);

}

/*
*  Fill in shipping Town/City field
*/
function lpac_fill_in_shipping_town_city(results) {

	const shipping_city = document.querySelector('#shipping_city');

	if (typeof (shipping_city) === 'undefined' || shipping_city === null) {
		return;
	}

	shipping_city.value = lpac_get_town_city(results);

}

/*
*  Fill in billing Town/City field
*/
function lpac_fill_in_billing_town_city(results) {

	const billing_city = document.querySelector('#billing_city');

	if (typeof (billing_city) === 'undefined' && billing_city === null) {
		return;
	}

	billing_city.value = lpac_get_town_city(results);

}

/*
*  Fill in shipping State/County field
*/
function lpac_fill_in_shipping_state_county(results) {

	/*
	* If we have values in our lpac_get_state_county() function
	*/
	if ( lpac_get_state_county(results) ) {

		/*
		* This field changes based on the country.
		* For some countries WC shows a text input and others it shows a dropdown
		* We need to get the field everytime or risk JS not being able to set it.
		*/
		const shipping_state_field = document.querySelector('#shipping_state');

		if (typeof (shipping_state_field) === 'undefined' || shipping_state_field === null) {
			return;
		}

		if (shipping_state_field.classList.contains('select2-hidden-accessible')) {

			shipping_state_field.value = lpac_get_state_county(results).short_name

			shipping_state_field.dispatchEvent(new Event('change', { 'bubbles': true })) // ensure Select2 sees the change

		} else {

			shipping_state_field.value = lpac_get_state_county(results).long_name

		}
	}

}


/*
*  Fill in billing State/County field
*/
function lpac_fill_in_billing_state_county(results) {

	if (lpac_get_state_county(results)) {

		/*
		* This field changes based on the country.
		* For some countries WC shows a text input and others it shows a dropdown
		* We need to get the field everytime or risk JS not being able to set it.
		*/
		const billing_state_field = document.querySelector('#billing_state');

		if (typeof (billing_state_field) === 'undefined' || billing_state_field === null) {
			return;
		}

		if (billing_state_field.classList.contains('select2-hidden-accessible')) {

			billing_state_field.value = lpac_get_state_county(results).short_name
			billing_state_field.dispatchEvent(new Event('change', { 'bubbles': true })) // ensure Select2 sees the change

		} else {

			billing_state_field.value = lpac_get_state_county(results).long_name

		}

	}

}

/*
*  Fill in shipping Zipcode field
*/
function lpac_fill_in_shipping_zipcode(results) {

	const shipping_zipcode = document.querySelector('#shipping_postcode');

	if (typeof (shipping_zipcode) === 'undefined' || shipping_zipcode === null) {
		return;
	}

	shipping_zipcode.value = lpac_get_zip_code(results);

}

/*
*  Fill in billing Zipcode field
*/
function lpac_fill_in_billing_zipcode(results) {

	const billing_zipcode = document.querySelector('#billing_postcode');

	if (typeof (billing_zipcode) === 'undefined' || billing_zipcode === null) {
		return;
	}

	billing_zipcode.value = lpac_get_zip_code(results);
}

/**
 * Show or hide the map.
 */
function changeMapVisibility(show){
	// console.log('show', show);
	if (show) {
		document.querySelector('#lpac-map-container').style.display = "block";
		document.querySelector('#lpac_is_map_shown').value = 1;
	} else {
		document.querySelector('#lpac-map-container').style.display = "none";
		document.querySelector('#lpac_is_map_shown').value = 0;
	}

}

/**
 * Ajax call to determine when the map should be shown or hidden.
 * 
 * See Lpac\Controllers::Map_Visibility_Controller
 */
function lpacHideShowMap(){

	wp.ajax.post( "lpac_checkout_map_visibility", {} )
  	.done(function(response) {
	
	const show = Boolean(response);
	changeMapVisibility(show);

 	})
  	.fail(function(response) {

	console.log(response);

  });

}

/**
 * Set the previous order marker.
 */
function lpacSetLastOrderMarker(){
	
	// Wait for map to load then add our marker
	google.maps.event.addListenerOnce( map, 'tilesloaded', function(){
		
		if( typeof (lpacLastOrder) === 'undefined' || lpacLastOrder === null ){
			return;
		}

		// If no coordinates exist don't try to plot the location on the map
		if( ! lpacLastOrder.latitude || ! lpacLastOrder.longitude ){
			return;
		}

		const latlng = {
			lat: parseFloat(lpacLastOrder.latitude),
			lng: parseFloat(lpacLastOrder.longitude),
		}

		let latitude = document.querySelector('#lpac_latitude');
		let longitude = document.querySelector('#lpac_longitude');

		if (typeof (latitude) === 'undefined' || latitude === null) {
			console.log('LPAC: Can\'t find latitude and longitude input areas. Can\'t insert location coordinates.');
		}
	
		if (typeof (longitude) === 'undefined' || longitude === null) {
			console.log('LPAC: Can\'t find latitude and longitude input areas. Can\'t insert location coordinates.');
		}
	
		latitude.value = lpacLastOrder.latitude
		longitude.value = lpacLastOrder.longitude

		map.setZoom(16);
		map.setCenter(latlng);
	
		marker.setPosition(latlng);

		// Only open the infowindow if we have a shipping address from the last order.
		if( lpacLastOrder.address ){
			infowindow.setContent(lpacLastOrder.address);
			infowindow.open(map, marker);
		}
		
		lpac_marker_listen_to_drag();
		lpac_map_listen_to_clicks();

	
	});

}

/**
 * Places Autocomplete feature.
 * 
 * https://developers.google.com/maps/documentation/javascript/examples/places-autocomplete
 * 
 * @returns 
 */
function addPlacesAutoComplete(){

	// Return if feature not enabled
	if( ! mapOptions.lpac_enable_places_autocomplete ){
		return;
	}

	// Hide map if option is turned on.
	if( mapOptions.lpac_places_autocomplete_hide_map ){
		changeMapVisibility(false);
	}

	const fields = mapOptions.lpac_places_autocomplete_fields;

	fields.forEach(fieldID => {

		const field = document.querySelector('#' + fieldID);

		/* 
		* If field doesn't exist bail.
		* This might happen if user sets shipping destination to "Force shipping to the customer billing address" so the shipping fields wouldn't exist.
		*/
		if( !field ){
			return;
		}

		const options = {
			fields: ["address_components", "formatted_address", "geometry"],
			types: ["address"],
		}

		/* 
		* Add country restrictions set in pro plugin settings 
		* lpac_pro_js is in global scope
		*/
		if( typeof (lpac_pro_js) !== 'undefined' && lpac_pro_js !== null){
			options.componentRestrictions = {
				country: lpac_pro_js.places_autocomplete_restrictions
			}
		}

		const autoComplete = new google.maps.places.Autocomplete(field, options);

		/* Bind the map's bounds (viewport) property to the autocomplete object,
		so that the autocomplete requests use the current map bounds for the
		bounds option in the request. */
		autoComplete.bindTo("bounds", map);

		autoComplete.addListener("place_changed", () => {
		
			const results = [autoComplete.getPlace()];

			const latlng = {
				lat: parseFloat( results[0].geometry.location.lat() ),
				lng: parseFloat( results[0].geometry.location.lng() ),
			}

			if( fieldID.includes('shipping') ){

				if( mapOptions.lpac_places_fill_shipping_fields ){
					lpac_fill_in_shipping_fields(results);
				}

				lpac_fill_in_latlng(latlng);

				map.setCenter(latlng);
				marker.setPosition(latlng);
				map.setZoom(16);
				infowindow.setContent(results[0].formatted_address);
				infowindow.open(map, marker);
				places_autocomplete_used.value = 1;
				// Add event listeners to map
				lpac_marker_listen_to_drag();
				lpac_map_listen_to_clicks();
			}else{
				
				if( mapOptions.lpac_places_fill_billing_fields ){
					lpac_fill_in_billing_fields(results);
				}

				/*
				* When Shipping destination is set as "Force shipping to the customer billing address" in WooCommerce->Shipping->Shipping Options
				* We would want to adjust the map as needed.
				*/
				if( mapOptions.lpac_wc_shipping_destination_setting  === 'billing_only' || ( fields.length === 1 && fields.includes('billing_address_1') ) ){
					lpac_fill_in_latlng(latlng);
					map.setCenter(latlng);
					marker.setPosition(latlng);
					map.setZoom(16);
					infowindow.setContent(results[0].formatted_address);
					infowindow.open(map, marker);
					places_autocomplete_used.value = 1;
					// Add event listeners to map
					lpac_marker_listen_to_drag();
					lpac_map_listen_to_clicks();
				}

			}

		})

	});

}
addPlacesAutoComplete();

/**
 * Detect when shipping methods are changed based on WC custom updated_checkout event.
 * This event can't be accessed via vanilla JS because it's triggered by jQuery. 
 */
(function ($) {
	'use strict';

	$(document).ready(
		function () {
			// Prevents ajax call in lpacHideShowMap from overriding our lpac_places_autocomplete_hide_map option.
			if( ! mapOptions.lpac_places_autocomplete_hide_map ){
				$(document.body).on('updated_checkout', lpacHideShowMap);
			}

			// If the auto detect location feature is turned on, then detect the location but don't output the last order details.
			if( mapOptions.lpac_auto_detect_location ){
				lpac_bootstrap_map_functionality(geocoder, map, infowindow)
			}else{
				lpacSetLastOrderMarker();
			}

		}
	);

})(jQuery);
