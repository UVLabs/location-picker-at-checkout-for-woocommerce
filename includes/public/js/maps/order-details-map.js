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
