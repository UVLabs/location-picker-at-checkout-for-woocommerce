function lpac_setup_order_details_map() {

	const map = window.lpac_map;
	const marker = window.lpac_marker;
	const infowindow = window.lpac_infowindow;

	if (typeof (map) === 'undefined' || map === null) {
		console.log('LPAC: map object not present, skipping...')
		return;
	}

	if (typeof (mapOptions) === 'undefined' || mapOptions === null) {
		console.log('LPAC: mapOptions object not present, skipping...')
		return;
	}

	map.setOptions(
		{
			center: { lat: mapOptions.lpac_map_order_latitude, lng: mapOptions.lpac_map_order_longitude },
			zoom: 16,
			draggableCursor: 'default',
			keyboardShortcuts: false,
			gestureHandling: 'none',
		}
	);

	const latlng = {
		lat: mapOptions.lpac_map_order_latitude,
		lng: mapOptions.lpac_map_order_longitude,
	};

	marker.setPosition(latlng);
	marker.setDraggable(false);
	marker.setCursor('default');

	// Only open the infowindow if we have a shipping address
	if( mapOptions.lpac_map_order_shipping_address_1 ){
		infowindow.setContent(`<p> ${mapOptions.lpac_map_order_shipping_address_1} <br/> ${mapOptions.lpac_map_order_shipping_address_2} </p>`);
		infowindow.open(map, marker);
	}

}

lpac_setup_order_details_map();
