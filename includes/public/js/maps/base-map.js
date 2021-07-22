	var map_id = '';

	if ( typeof lpac_pro_js !== 'undefined' ) {
		map_id = lpac_pro_js.map_id;
	}

	if( typeof(map_options) !== 'undefined' && map_options !== null ){

	var map = new google.maps.Map(
		document.querySelector( ".lpac-map" ),
		{
			center: { lat: map_options.lpac_map_default_latitude, lng: map_options.lpac_map_default_longitude },
			zoom: map_options.lpac_map_zoom_level,
			streetViewControl: false,
			clickableIcons: map_options.lpac_map_clickable_icons,
			backgroundColor: map_options.lpac_map_background_color, //loading background color
			mapId: map_id,
		}
	);

	}else{
		console.log('LPAC: map_options object not present, skipping...')
	}
