	var map_id = '';

	if ( typeof lpac_pro_js !== 'undefined' ) {
		map_id = lpac_pro_js.map_id;
	}

	const map = new google.maps.Map(
		document.querySelector( "#lpac-map" ),
		{
			center: { lat: coordinates.latitude, lng: coordinates.longitude },
			zoom: 16,
			streetViewControl: false,
			clickableIcons: false,
			backgroundColor: '#eee', //loading background color
			mapId: map_id,
		}
	);

	const latlng = {
		lat: coordinates.latitude,
		lng: coordinates.longitude,
	};

	const marker = new google.maps.Marker(
		{
			position: latlng,
			map: map,
			cursor: 'default'
			}
	);
