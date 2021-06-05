map.setOptions({

// Overwrite map options
// https://developers.google.com/maps/documentation/javascript/reference/map#MapOptions

});

const geocoder   = new google.maps.Geocoder();
const infowindow = new google.maps.InfoWindow();

document.getElementById( "lpac-find-location-btn" ).addEventListener(
    "click",
    () => {
        geocodeLatLng( geocoder, map, infowindow );
    }
);

function getCoordinates() {

	return new Promise(
		function(resolve, reject) {
			//TODO return reject if navigator not present
			if (navigator.geolocation) {
				  navigator.geolocation.getCurrentPosition( resolve, reject );
			}

		}
	);

}

// Globally scoped so that only one marker can be added to map.
const marker = new google.maps.Marker(
    {
        draggable: true,
        map: map,
        }
    );

  async function geocodeLatLng(geocoder, map, infowindow) {

	const position = await this.getCoordinates();

	latitude  = position.coords.latitude;
	longitude = position.coords.longitude;

	const latlng = {
		lat: parseFloat( latitude ),
		lng: parseFloat( longitude ),
		};

		geocoder.geocode(
			{ location: latlng },
			(results, status) => {
            if (status === "OK") {
                if (results[0]) {

                    map.setZoom( 16 );
                    map.setCenter( { lat: latitude, lng: longitude } );

                    marker.setPosition(latlng)
                        let detected_address = results[0].formatted_address;

                        infowindow.setContent( detected_address );
                        infowindow.open( map, marker );

                        // document.querySelector('#current-address').innerHTML = detected_address;

                        // When Marker is Moved/Dragged
                        google.maps.event.addListener(
                        marker,
                        'dragend',
                        function (event) {

                            const moved_to_lat = event.latLng.lat();
                            const moved_to_lng = event.latLng.lng();

                            const moved_to_latlng = {
                                lat: parseFloat( moved_to_lat ),
                                lng: parseFloat( moved_to_lng ),
                            };

                            // NOTE: This can cause a "GEOCODER_GEOCODE: OVER_QUERY_LIMIT" console error
                            // When the user is trying to drag the marker too quickly
                            // Fixes include not using goecoding for finding updated address 
                            // geocoder.geocode(
                            // { location: moved_to_latlng },
                            // (results, status) => {
                            //     if( results && status === "OK" ){
                            //         let moved_to_address = results[0].formatted_address;
                            //         infowindow.setContent( moved_to_address );
                            //     }
                            // }
                            // );

                            infowindow.close();

                            document.querySelector( '#lpac_latitude' ).value  = moved_to_lat;
                            document.querySelector( '#lpac_longitude' ).value = moved_to_lng;

                            map.panTo( event.latLng );
                        }
                    );

                } else {
                    window.alert( "No results found" );
                }
            } else {
					window.alert( "Geocoder failed due to: " + status );
            }

			}
		);

	async function output_details(){

		document.querySelector( '#lpac_latitude' ).value  = latitude;
		document.querySelector( '#lpac_longitude' ).value = longitude;

	}

	output_details()

  }
