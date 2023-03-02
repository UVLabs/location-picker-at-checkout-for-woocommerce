/**
 * Globals:
 * mapOptions, locationDetails
 */
import {
  initializeInfoWindow,
  initializeMap,
  initializeMarker,
} from "../../js-modules/utils/initialize-map.js";

function lpacSetupShopOrderMap() {
  const mapConfig = {
    mapId: googleMapID,
  };
  const map = initializeMap(mapConfig);
  map.setMapTypeId(mapOptions.lpac_admin_order_screen_default_map_type);

  /**
   * This variable is defined in output_custom_order_details_metabox().
   *
   * It does not exist when in cases where lat and long might not be present for an order.
   */
  if (typeof locationDetails === "undefined" || locationDetails === null) {
    return;
  }

  map.setOptions({
    center: { lat: locationDetails.latitude, lng: locationDetails.longitude },
    zoom: 16,
    streetViewControl: false,
    clickableIcons: false,
    backgroundColor: "#eee", //loading background color
  });

  const latlng = {
    lat: locationDetails.latitude,
    lng: locationDetails.longitude,
  };

  const markerOptions = {
    clickable: false,
    position: latlng,
    map: map,
  };

  const marker = initializeMarker(markerOptions);
  const infoWindow = initializeInfoWindow();

  // Only open the infowindow if we have a shipping address
  if (locationDetails.shipping_address_1) {
    infoWindow.setContent(
      `<p> ${locationDetails.shipping_address_1} <br/> ${locationDetails.shipping_address_2} </p>`
    );
    infoWindow.open(map, marker);
  }
}

lpacSetupShopOrderMap();
