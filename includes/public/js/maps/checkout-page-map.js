// map.setOptions({
//    disableDefaultUI: true,
//  Overwrite map options
//  https://developers.google.com/maps/documentation/javascript/reference/map#MapOptions
// })

const geocoder   = new google.maps.Geocoder()
const infowindow = new google.maps.InfoWindow()

document.getElementById( "lpac-find-location-btn" ).addEventListener(
    "click",
    () => {
        geocodeLatLng( geocoder, map, infowindow )
    }
)

function get_coordinates() {

	return new Promise(
		function(resolve, reject) {

            if (navigator.geolocation) {
				  navigator.geolocation.getCurrentPosition( resolve, reject )
			}else{
                // TODO add input fields so users can change this text
                alert('Geolocation is not possible on this web browser. Please switch to a different web browser to use our interactive map.');
            }

		}
	).catch(function(error){
        
        console.log('Location Picker At Checkout Plugin: ' + error.message)

        if( error.code === 1 ){
            // TODO add input fields so users can change this text
            alert("Something went wrong while trying to detect your location. Click on the location icon in the address bar and allow our website to detect your location. Please contact us if you need additional assistance.");
            return
        }

        alert(error.message);

    })

}

    /**  <Global Settings> */
    // Globally scoped so that only one marker can be added to map.
    const marker = new google.maps.Marker(
        {
        draggable: true,
        map: map,
        }
    )
    
    var lpac_autofill_billing_fields = map_options.lpac_autofill_billing_fields
    /**  </Global Settings> */
    

  async function geocodeLatLng(geocoder, map, infowindow) {

	const position = await this.get_coordinates()

    if( ! position ){
        console.log('Location Picker At Checkout Plugin: Position object is empty. Navigator might be disabled or this site might be detected as insecure.')
        return;
    }

	latitude  = position.coords.latitude
	longitude = position.coords.longitude

	const latlng = {
		lat: parseFloat( latitude ),
		lng: parseFloat( longitude ),
		}

		geocoder.geocode(
			{ location: latlng },
			(results, status) => {
            if (status === "OK") {
                if (results[0]) {

                    map.setZoom( 16 )
                    map.setCenter( { lat: latitude, lng: longitude } )

                    marker.setPosition(latlng)
                    
                    let detected_address = results[0].formatted_address

                    infowindow.setContent( detected_address )
                    infowindow.open( map, marker )

                    // document.querySelector('#current-address').innerHTML = detected_address
                    console.log(results)
                    lpac_fill_in_address_fields(results)
                    // When Marker is Moved/Dragged
                    google.maps.event.addListener(
                    marker,
                    'dragend',
                    function (event) {

                        const moved_to_lat = event.latLng.lat()
                        const moved_to_lng = event.latLng.lng()

                        const moved_to_latlng = {
                            lat: parseFloat( moved_to_lat ),
                            lng: parseFloat( moved_to_lng ),
                        }

                        geocoder.geocode(
                        { location: moved_to_latlng },
                        (results, status) => {
                            // Debug
                            console.log(results)
                            if( status === "OK" ){
                            let moved_to_address = results[0].formatted_address
                            lpac_fill_in_address_fields(results)
                            infowindow.setContent( moved_to_address )
                            }
                        }
                        ).then( function(resolved){
                            // infowindow.close()
                            document.querySelector( '#lpac_latitude' ).value  = moved_to_lat
                            document.querySelector( '#lpac_longitude' ).value = moved_to_lng
    
                            map.panTo( event.latLng )
                            

                        }).catch( function(error){
                            
                            console.log(error)
                            // TODO Add error messages below map
                            
                            if( error.code === 'OVER_QUERY_LIMIT' ){
                            error_msg = 'You are moving the map marker too quickly, use the zoom out button to move the marker across larger distances.'
                            alert(error_msg)
                            location.reload()
                            }

                        })

                    }
                    )

                } else {
                    window.alert( "No results found" )
                }
            } else {
					window.alert( "Geocoder failed due to: " + status )
            }

			}
		)

	async function output_details(){

		document.querySelector( '#lpac_latitude' ).value  = latitude
		document.querySelector( '#lpac_longitude' ).value = longitude

	}

	output_details()

  }

function lpac_fill_in_address_fields( results ){

    lpac_fill_in_country_region( results )
    lpac_fill_in_full_address( results )
    lpac_fill_in_town_city( results )
    lpac_fill_in_state_county( results )
    lpac_fill_in_zipcode( results )

}

// Fill in street country field
function lpac_fill_in_country_region( results ){

    var country = ''
    const country_array = results[0].address_components.find(addr => addr.types[0] === "country")
    
    if( country_array ){
        country = country_array.short_name
    }

    document.querySelector('#shipping_country').value = country
    document.querySelector('#shipping_country').dispatchEvent(new Event('change', { 'bubbles': true })) // ensure Select2 sees the change

    if(lpac_autofill_billing_fields){
    document.querySelector('#billing_country').value = country
    document.querySelector('#billing_country').dispatchEvent(new Event('change', { 'bubbles': true })) // ensure Select2 sees the change
    }

}

// Fill in street address field
function lpac_fill_in_full_address( results ){

    const full_address = results[0].formatted_address
    document.querySelector('#shipping_address_1').value = full_address

    if(lpac_autofill_billing_fields){
        document.querySelector('#billing_address_1').value = full_address
    }

}

// Fill in Town / City field
function lpac_fill_in_town_city( results ){

    var town_city = ''
    const town_city_array = results[0].address_components.find(addr => addr.types[0] === "locality") 
    const town_city_array2 = results[0].address_components.find(addr => addr.types[0] === "postal_town") 

    if( town_city_array ){
        town_city = town_city_array.long_name
    }

    // Overwrite with more standard "postal_town" field if it exists
    if( town_city_array2 ){
        town_city = town_city_array2.long_name
    }

    document.querySelector('#shipping_city').value = town_city

    if(lpac_autofill_billing_fields){
        document.querySelector('#billing_city').value = town_city
    }

}

// Fill in State / County field
function lpac_fill_in_state_county( results ){
   
    var state_county = ''
    const state_county_array = results[0].address_components.find(addr => addr.types[0] === "administrative_area_level_1")
    
    if( state_county_array ){
        state_county = state_county_array.long_name
    }

    var shipping_state_field = document.querySelector('#shipping_state')
      
    if( shipping_state_field.classList.contains('select2-hidden-accessible') ){

        if( state_county_array ){
            shipping_state_field.value = state_county_array.short_name
            shipping_state_field.dispatchEvent(new Event('change', { 'bubbles': true })) // ensure Select2 sees the change
        }
    
    }else{
        shipping_state_field.value = state_county
    }

    if(lpac_autofill_billing_fields){

        var billing_state_field = document.querySelector('#billing_state')
        
        if( billing_state_field.classList.contains('select2-hidden-accessible') ){

            if( state_county_array ){
                billing_state_field.value = state_county_array.short_name
                billing_state_field.dispatchEvent(new Event('change', { 'bubbles': true })) // ensure Select2 sees the change
            }
        
        }else{
            billing_state_field.value = state_county
        }

    }
}

// Fill in Zipcode field
function lpac_fill_in_zipcode( results ){
    
    var zipcode = ''
    const zipcode_array = results[0].address_components.find(addr => addr.types[0] === "postal_code")

    if( zipcode_array ){
        zipcode = zipcode_array.short_name
    }

    document.querySelector('#shipping_postcode').value = zipcode
    
    if(lpac_autofill_billing_fields){
        document.querySelector('#billing_postcode').value = zipcode
    }

}