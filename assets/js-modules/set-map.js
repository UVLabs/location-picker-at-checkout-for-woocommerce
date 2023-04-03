// Globals: google
import { fillAllAddressFields } from "./checkout-page/fill-fields.js";
import { removePlusCode } from "./utils/address-components.js";

/**
 * Set our map center, marker and infowindow data.
 *
 * Map data is an array of [map, marker, latlng, results, infowindow]
 *
 * @param {object} mapData
 */
export function setMap(mapData) {
  const map = mapData["map"];
  const marker = mapData["marker"];
  const latlng = mapData["latlng"];
  const results = mapData["results"];
  const infowindow = mapData["infowindow"];

  map.setCenter(latlng);
  // map.panTo(latlng);
  marker.setPosition(latlng);
  map.setZoom(16);
  infowindow.setContent(results[0].formatted_address);
  infowindow.open(map, marker);
}

/**
 * Get Lat and Long cords from browser navigator.
 */
export function getNavigatorCoordinates() {
  return new Promise(function (resolve, reject) {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(resolve, reject);
    } else {
      // TODO add input fields so users can change this text
      alert(lpacTranslatedJsStrings.geolocation_not_supported);
    }
  }).catch(function (error) {
    console.log("Location Picker At Checkout Plugin: " + error.message);

    if (error.code === 1) {
      // TODO add input fields so users can change this text
      alert(lpacTranslatedJsStrings.manually_select_location);
      return;
    }

    alert(error.message);
  });
}

/**
 * Bootstrap the functionality of the map.
 *
 * @param mapData the data for manipulating the map.
 * @returns latLng The latitude and longitude.
 * @since 1.7.0
 */
export async function bootstrapMapFunctionality(mapData) {
  const position = await getNavigatorCoordinates();
  let latLng = "";

  if (position) {
    var latitude = position.coords.latitude;
    var longitude = position.coords.longitude;
  } else {
    console.log(
      "Location Picker At Checkout Plugin: Position object is empty. Navigator might be disabled or this site might be detected as insecure."
    );

    const currentZoom = mapData.map.getZoom();
    if (currentZoom < 13) {
      mapData.map.setZoom(13);
    }

    // Make sure a user is able to select their location on the map when they have blocked access to their location.
    listenToMapClicks(mapData);
    listenToMapDrag(mapData);

    /**
     * We're setting this to '' so that we can force the user to make use of the map if the option is enabled.
     * So that if we do not receive a location, the user can enter one manually.
     *
     * Note that doing this means we have to be sure to add the event listeners for drag and click to the map when
     *
     */
    return (latLng = {
      lat: "",
      lng: "",
    });
  }

  latLng = {
    lat: parseFloat(latitude),
    lng: parseFloat(longitude),
  };

  return latLng;
}

/**
 * This is a special function created for jQuery to bootstrap the map setup when the option to auto detect the customer location is turned on.
 *
 * I didnt want to make the main jQuery function async so I ported the code needed to bootstrap the map into this function.
 *
 * @param {object} mapData
 */
export async function bootstrapMapFunctionalityJQuery(mapData) {
  const latLng = await bootstrapMapFunctionality(mapData);

  if (latLng.lat !== "" && latLng.lng !== "") {
    const map = mapData.map;
    const geocodeResults = await geocodeCoordinates(latLng, map);
    // We need some additional details when passing to setupMap.
    mapData.geocodeResults = geocodeResults;
    mapData.latLng = latLng;
    setupMap(mapData);
    fillAllAddressFields(geocodeResults);
  }

  fillLatLong(latLng, mapOptions);
}

/**
 * Setup the intial map and marker location and listening of events.
 *
 * Map data is an array of [map, mapOptions, marker, latlng, infowindow]
 *
 * @param {object} mapData
 */
export function setupMap(mapData) {
  const map = mapData.map;
  const marker = mapData.marker;
  const latLng = mapData.latLng;
  const infowindow = mapData.infowindow;
  const results = mapData.geocodeResults;

  if (!results[0]) {
    return;
  }

  map.setZoom(16);
  map.setCenter(latLng);
  marker.setPosition(latLng);

  let detected_address = results[0].formatted_address;

  detected_address = removePlusCode(detected_address);

  infowindow.setContent(detected_address);
  infowindow.open(map, marker);

  listenToMapClicks(mapData);
  listenToMapDrag(mapData);
}

/**
 * Fill in Latitude and Longitude fields.
 *
 * @param {object} latLng
 * @param {object} mapOptions
 * @returns
 */
export function fillLatLong(latLng, mapOptions) {
  //TODO Move this to more appropriate module like a new one called fill-fields in root folder.

  if (latLng.lat === "" || latLng.lng === "") {
    console.log(
      "Location Picker At Checkout Plugin: Empty latLng. See fillLatLong()"
    );
  }

  let latitude = document.querySelector("#lpac_latitude");
  let longitude = document.querySelector("#lpac_longitude");

  if (typeof latitude === "undefined" || latitude === null) {
    console.log(
      "LPAC: Can't find latitude and longitude input areas. Can't insert location coordinates."
    );
    return;
  }

  if (typeof longitude === "undefined" || longitude === null) {
    console.log(
      "LPAC: Can't find latitude and longitude input areas. Can't insert location coordinates."
    );
    return;
  }

  latitude.value = latLng.lat;
  longitude.value = latLng.lng;

  latitude.dispatchEvent(new Event("input", { bubbles: false }));
  longitude.dispatchEvent(new Event("input", { bubbles: false }));

  if (mapOptions.fill_in_fields === false) {
    /**
     * Ensure that this event is fired and the checkout is updated.
     *
     * If the filter(lpac_fill_checkout_fields) to not fill in address fields is set to true, we need to call this event here so that the cart can update and
     * operations such as the cost by distance feature that require the new Lat and Long can be updated accordingly to show the new price.
     *
     * If we didn't do this then WooCommerce wouldn't realize the change in our custom lat and long fields so it wouldnt refresh.
     */
    // TODO move this condition outside of this function since we might not always be filling in these specific fields.
    if (jQuery) {
      jQuery(document.body).trigger("update_checkout");
    }
  }
}

/**
 * Function getting address details from latitude and longitude.
 *
 * @param {object} latLng Latitude and Longitude
 * @param {object} map Instance of Map
 * @returns object
 */
export async function geocodeCoordinates(latLng, map) {
  let address_array = "";
  const geocoder = new google.maps.Geocoder();

  await geocoder
    .geocode({ location: latLng }, (results, status) => {
      console.log(results);
      if (status === "OK") {
        if (results[0]) {
          address_array = results;
        } else {
          window.alert(lpacTranslatedJsStrings.no_results_found);
          return;
        }
      } else {
        console.log("Geocoder failed due to: " + status);
        return;
      }
    })
    .then(function (resolved) {
      map.panTo(latLng);
    })
    .catch(function (error) {
      console.log(error);
      // TODO Add error messages below map

      if (error.code === "OVER_QUERY_LIMIT") {
        alert(lpacTranslatedJsStrings.moving_too_quickly);
        location.reload();
      }

      if (error.code === "UNKNOWN_ERROR") {
        alert(lpacTranslatedJsStrings.generic_error);
        location.reload();
      }
    });

  return address_array;
}

/**
 * Map data is an array of [map, mapOptions, marker, infowindow]
 * @param {object} mapData
 */
export function listenToMapClicks(mapData) {
  const map = mapData.map;
  const mapOptions = mapData.mapOptions;
  const marker = mapData.marker;
  const infowindow = mapData.infowindow;

  /**
   * Clear previous event listeners of this type before adding this new one.
   *
   * This is the only function where we're defining this event listener for the map, so it's fine to remove it this way.
   * https://developers.google.com/maps/documentation/javascript/events#removing
   */
  google.maps.event.clearListeners(map, "click");

  const places_autocomplete_used = document.querySelector(
    "#lpac_places_autocomplete"
  );

  map.addListener("click", async function (event) {
    const results = await geocodeCoordinates(event.latLng, map);

    if (!results[0]) {
      console.log("LPAC: Results not as expected. See listenToMapClicks()");
      return;
    }

    const lat = event.latLng.lat();
    const lng = event.latLng.lng();

    const latLng = {
      lat: parseFloat(lat),
      lng: parseFloat(lng),
    };

    // We might have to update this in the future because the page where a map lives might not necessarily have address fields.
    fillAllAddressFields(results);
    fillLatLong(latLng, mapOptions);

    marker.setPosition(event.latLng);

    let detected_address = results[0].formatted_address;

    detected_address = removePlusCode(detected_address);

    infowindow.setContent(detected_address);
    infowindow.open(map, marker);

    places_autocomplete_used.value = 0;
  });
}

/**
 * Map data is an array of [map, mapOptions, marker, infowindow]
 * @param {object} mapData
 */
export function listenToMapDrag(mapData) {
  const map = mapData.map;
  const mapOptions = mapData.mapOptions;
  const marker = mapData.marker;
  const infowindow = mapData.infowindow;

  /**
   * Clear previous event listeners of this type before adding this new one.
   *
   * This is the only function where we're defining this event listener for the marker, so it's fine to remove it this way.
   * https://developers.google.com/maps/documentation/javascript/events#removing
   */
  google.maps.event.clearListeners(marker, "dragend");

  const places_autocomplete_used = document.querySelector(
    "#lpac_places_autocomplete"
  );

  google.maps.event.addListener(marker, "dragend", async function (event) {
    const moved_to_lat = event.latLng.lat();
    const moved_to_lng = event.latLng.lng();

    const latlng = {
      lat: parseFloat(moved_to_lat),
      lng: parseFloat(moved_to_lng),
    };

    let results = await geocodeCoordinates(latlng, map);

    if (!results[0]) {
      console.log("Results not as expected. See lpac_marker_listen_to_drag()");
      return;
    }

    let moved_to_address = results[0].formatted_address;

    moved_to_address = removePlusCode(moved_to_address);

    infowindow.setContent(moved_to_address);

    // We might have to update this in the future because the page where a map lives might not necessarily have address fields.
    fillAllAddressFields(results);

    fillLatLong(latlng, mapOptions);
    places_autocomplete_used.value = 0;
  });
}
