/**
 * Initialize/Bootstrap the Google Map and return the created object for further processing.
 *
 * @param {object} mapConfig May be empty in situations where we're later assigning the config via .setOptions()
 * @param {string} mapDiv
 * @returns {object} Instance(s) of Google Map
 */
export function initializeMap(
  mapConfig = {
    center: {
      lat: mapOptions.lpac_map_default_latitude,
      lng: mapOptions.lpac_map_default_longitude,
    },
    zoom: mapOptions.lpac_map_zoom_level,
    streetViewControl: false,
    clickableIcons: mapOptions.lpac_map_clickable_icons,
    backgroundColor: mapOptions.lpac_map_background_color, //loading background color
  },
  mapDiv = ""
) {
  mapDiv = mapDiv ? mapDiv : "lpac-map";

  const el = document.querySelector(`.${mapDiv}`);

  if (!el) {
    return;
  }

  return new google.maps.Map(el, mapConfig); // Maps are initialized here, as soon as new google.maps.Map() is called.
}

/**
 * Initialize an instance of the Google Marker that would be placed on a map.
 *
 * @param {object} markerOptions
 * @returns Google Map Maker instance
 */
export function initializeMarker(markerOptions = {}) {
  return new google.maps.Marker(markerOptions);
}

/**
 * Initialize an instance of the Google InfoWinow that would be placed on a map.
 *
 * @param {object} infoWindowOptions
 * @returns Google InfoWindow instance
 */
export function initializeInfoWindow(
  infoWindowOptions = { disableAutoPan: true }
) {
  return new google.maps.InfoWindow(infoWindowOptions);
}
