// Google Maps ID
	var map_id = '';
if ( map_options.lpac_map_saved_map_id ) {
	map_id = map_options.lpac_map_saved_map_id;
}

	const map = new google.maps.Map(
		document.querySelector( ".lpac-map" ),
		{
			center: { lat: map_options.lpac_map_default_latitude, lng: map_options.lpac_map_default_longitude },
			zoom: map_options.lpac_map_zoom_level,
			clickableIcons: map_options.lpac_map_clickable_icons,
			backgroundColor: map_options.lpac_map_background_color, //loading background color
			mapId: map_id,
		}
	);
