import {
  initializeInfoWindow,
  initializeMap,
} from "../../../js-modules/utils/initialize-map.js";

let google_map_id = "";
window.lpac_map_marker_image = "";

if (typeof lpac_pro_js !== "undefined" && lpac_pro_js !== null) {
  google_map_id = lpac_pro_js.google_map_id;

  if (lpac_pro_js.marker_icon) {
    const marker_icon_width = lpac_pro_js.marker_icon_width;
    const marker_icon_height = lpac_pro_js.marker_icon_height;
    let x_anchor = lpac_pro_js.marker_icon_anchor_x;
    let y_anchor = lpac_pro_js.marker_icon_anchor_y;

    if (x_anchor.length == 0) {
      // Likely values to make the anchor appear ideal.
      // The x axis anchor is usually half of the image width, the y is usually 3px over the image height
      x_anchor = marker_icon_width / 2;
      y_anchor = marker_icon_height + 3;
    }

    window.lpac_map_marker_image = {
      url: lpac_pro_js.marker_icon,
      size: new google.maps.Size(marker_icon_width, marker_icon_height),
      origin: new google.maps.Point(0, 0),
      anchor: new google.maps.Point(x_anchor, y_anchor),
    };
  }
}

/**
 * Global mapOptions variable is set in Lpac\Views\Frontend::setup_global_js_vars
 */
if (
  (typeof mapOptions !== "undefined" && mapOptions !== null) ||
  (typeof locationDetails !== "undefined" && locationDetails !== null)
) {
  /**
   * <Global Settings>
   */
  const mapConfig = {
    center: {
      lat: mapOptions.lpac_map_default_latitude,
      lng: mapOptions.lpac_map_default_longitude,
    },
    zoom: mapOptions.lpac_map_zoom_level,
    streetViewControl: false,
    clickableIcons: mapOptions.lpac_map_clickable_icons,
    backgroundColor: mapOptions.lpac_map_background_color, //loading background color
    mapId: google_map_id,
    // If the option is in the array then that means we want to hide it. So we need to negate the outcome to get the correct behaviour.
    mapTypeControl: mapOptions.disabled_map_controls
      ? !mapOptions.disabled_map_controls.includes("maptype")
      : true,
    zoomControl: mapOptions.disabled_map_controls
      ? !mapOptions.disabled_map_controls.includes("zoom")
      : true,
    fullscreenControl: mapOptions.disabled_map_controls
      ? !mapOptions.disabled_map_controls.includes("fullscreen")
      : true,
  };

  /**
   * Always use the lpac-map instance when working with the checkout page map.
   * there might be other map instances on the page created by the Map Builder
   * but for this logic we specifically want the one for our checkout page map, not a shortcode.
   */
  const map = initializeMap(mapConfig);

  /* Globally scoped so that only one marker for customer location can be added to map. */
  const marker = new google.maps.Marker({
    draggable: true,
    map: map,
    icon: window.lpac_map_marker_image,
  });

  /* Globally scoped so that only one info window for customer location can be added to map. */
  const infowindow = initializeInfoWindow();

  /* We need to set these variables to the window object or else parcel will break our script when transpiling */
  window.lpac_map = map;
  window.lpac_marker = marker;
  window.lpac_infowindow = infowindow;

  /**
   * </Global Settings>
   */
} else {
  console.log("LPAC: mapOptions object not present, skipping...");
}
