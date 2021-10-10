let map_id = '';

if (typeof lpac_pro_js !== 'undefined') {
	map_id = lpac_pro_js.map_id;
}

/**
 * Global map_options variable is set in Lpac\Views\Frontend::lpac_expose_map_settings_js
 */
if (typeof (map_options) !== 'undefined' && map_options !== null) {

	/**
	* <Global Settings>
	*/
	const map = new google.maps.Map(
		document.querySelector(".lpac-map"),
		{
			center: { lat: map_options.lpac_map_default_latitude, lng: map_options.lpac_map_default_longitude },
			zoom: map_options.lpac_map_zoom_level,
			streetViewControl: false,
			clickableIcons: map_options.lpac_map_clickable_icons,
			backgroundColor: map_options.lpac_map_background_color, //loading background color
			mapId: map_id,
		}
	);

	/* Globally scoped so that only one marker can be added to map. */
	const marker = new google.maps.Marker(
		{
			draggable: true,
			map: map,
		}
	);

	/* Globally scoped so that only one info window can be added to map. */
	const infowindow = new google.maps.InfoWindow();

	/* We need to set these variables to the window object or else parcel will break our script when transpiling */
	window.lpac_map = map;
	window.lpac_marker = marker;
	window.lpac_infowindow = infowindow;

} else {
	console.log('LPAC: map_options object not present, skipping...')
}
