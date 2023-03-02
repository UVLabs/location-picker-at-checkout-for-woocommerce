import { initializeInfoWindow, initializeMarker } from "./initialize-map.js";

/**
 * Plot passed store locations on the passed map.
 *
 * @param {object} map
 * @param {object} storeLocations
 */
export function plotStoreLocations(map, storeLocations) {
  // Manipulate our store locations object to display the different locations and their labels
  Object.keys(storeLocations).forEach((key) => {
    const location = storeLocations[key];
    const locationCordsArray = location.store_cords_text.split(",");
    const latitude = locationCordsArray[0];
    const longitude = locationCordsArray[1];

    const latlng = {
      lat: parseFloat(latitude),
      lng: parseFloat(longitude),
    };

    const markerOptions = {
      clickable: false,
      icon:
        typeof lpac_pro_js !== "undefined" && lpac_pro_js.is_pro
          ? location.store_icon_text
          : "", // show icon only in pro
      position: latlng,
      map: map,
    };

    const infoWindowConfig = {
      content: location.store_name_text,
      disableAutoPan: true,
    };

    const marker = initializeMarker(markerOptions);
    const infoWindow = initializeInfoWindow(infoWindowConfig);

    infoWindow.open(map, marker);
  });
}
