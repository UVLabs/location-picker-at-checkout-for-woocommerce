
function lpacSetupShopOrderMap() {

	const map = window.lpac_map;
	const marker = window.lpac_marker;
	const infowindow = window.lpac_infowindow;

	/**
	 * This variable is defined in lpac_output_custom_order_details_metabox().
	 * 
	 * It does not exist when in cases where lat and long might not be present for an order.
	 */
	if (typeof (locationDetails) === 'undefined' || locationDetails === null) {
		return;
	}

	map.setOptions(
		{
			center: { lat: locationDetails.latitude, lng: locationDetails.longitude },
			zoom: 16,
			streetViewControl: false,
			clickableIcons: false,
			backgroundColor: '#eee', //loading background color
		}
	);

	const latlng = {
		lat: locationDetails.latitude,
		lng: locationDetails.longitude,
	};

	marker.setPosition(latlng);
	marker.setDraggable(false);
	marker.setCursor('default');

	infowindow.setContent(`<p> ${locationDetails.shipping_address_1} <br/> ${locationDetails.shipping_address_2} </p>`);
	infowindow.open(map, marker);

}

lpacSetupShopOrderMap();