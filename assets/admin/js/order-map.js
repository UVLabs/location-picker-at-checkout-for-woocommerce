var map_id = '';

if (typeof lpac_pro_js !== 'undefined') {
	map_id = lpac_pro_js.map_id;
}

function lpacSetupShopOrderMap() {

	/**
	 * This variable is defined in lpac_output_custom_order_details_metabox().
	 * 
	 * It does not exist when in cases where lat and long might not be present for an order.
	 */
	if (typeof (locationDetails) === 'undefined' || locationDetails === null) {
		return;
	}

	const map = new google.maps.Map(
		document.querySelector(".lpac-map"),
		{
			center: { lat: locationDetails.latitude, lng: locationDetails.longitude },
			zoom: 16,
			streetViewControl: false,
			clickableIcons: false,
			backgroundColor: '#eee', //loading background color
			mapId: map_id,
		}
	);

	const latlng = {
		lat: locationDetails.latitude,
		lng: locationDetails.longitude,
	};

	const marker = new google.maps.Marker(
		{
			position: latlng,
			map: map,
			cursor: 'default'
		}
	);

	const infowindow = new google.maps.InfoWindow();
	infowindow.setContent(`<p> ${locationDetails.shipping_address_1} <br/> ${locationDetails.shipping_address_2} </p>`);
	infowindow.open(map, marker);

}

lpacSetupShopOrderMap();