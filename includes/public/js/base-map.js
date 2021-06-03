// TODO add error message if saved_coordinates object not found.
const latitude  = saved_coordinates.latitude;
const longitude = saved_coordinates.longitude;

function initMap() {

	const map = new google.maps.Map(
		document.querySelector( ".lpac-map" ),
		{
			zoom: 16,
			clickableIcons: false,
			mapId: 'a64c229d17399b09',
			backgroundColor: '#eee', //loading background color
			disableDefaultUI: true,
			keyboardShortcuts: false,
			draggableCursor: 'default',
			gestureHandling: 'none',
			//   zoomControl: false,
			//   fullscreenControl: false,
			//   mapTypeId: 'satellite'/'hybrid'/'roadmap' (also cycling etc)
			center: { lat: latitude, lng: longitude },
		}
	);

	const latlng = {
		lat: latitude,
		lng: longitude,
	};

	const marker = new google.maps.Marker(
		{
			position: latlng,
			map: map,
			// icon: 'https://img.icons8.com/color/pin',
			animation: google.maps.Animation.BOUNCE,
			cursor: 'default'
		}
	);

	  // marker.setAnimation(google.maps.Animation.DROP);
	  // marker.setAnimation(700);
	// const geocoder = new google.maps.Geocoder();
	// const infowindow = new google.maps.InfoWindow();

}
