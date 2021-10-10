function lpac_setup_order_received_map() {

	const map = window.lpac_map;
	const infowindow = window.lpac_infowindow;

	if (typeof (map) === 'undefined' || map === null) {
		console.log('LPAC: map object not present, skipping...')
		return;
	}

	if (typeof (map_options) === 'undefined' || map_options === null) {
		console.log('LPAC: map_options object not present, skipping...')
		return;
	}

	map.setOptions(
		{
			center: { lat: map_options.lpac_map_order_latitude, lng: map_options.lpac_map_order_longitude },
			zoom: 16,
			draggableCursor: 'default',
			keyboardShortcuts: false,
			gestureHandling: 'none',
		}
	);

	const latlng = {
		lat: map_options.lpac_map_order_latitude,
		lng: map_options.lpac_map_order_longitude,
	};

	const marker = new google.maps.Marker(
		{
			position: latlng,
			map: map,
			cursor: 'default'
		}
	);

	infowindow.setContent(`<p> ${map_options.lpac_map_order_shipping_address_1} <br/> ${map_options.lpac_map_order_shipping_address_2} </p>`);
	infowindow.open(map, marker);

}

lpac_setup_order_received_map();
