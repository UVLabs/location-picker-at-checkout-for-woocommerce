import {
  initializeInfoWindow,
  initializeMap,
  initializeMarker,
} from "../../../js-modules/utils/initialize-map.js";

/**
 * Globals:
 *
 * mapOptions
 */
function setupOrderReceivedMap() {
  if (typeof mapOptions === "undefined" || mapOptions === null) {
    console.log("LPAC: mapOptions object not present, skipping...");
    return;
  }

  let map = {};

  if (typeof lpac_pro_js !== "undefined" && lpac_pro_js !== null) {
    const google_map_id = lpac_pro_js.google_map_id ?? "";

    const mapConfig = {
      mapId: google_map_id,
    };

    map = initializeMap(mapConfig);
  } else {
    map = initializeMap();
  }

  if (typeof map === "undefined" || map === null) {
    console.log("LPAC: map object not present, skipping...");
    return;
  }

  map.setMapTypeId(mapOptions.lpac_thank_you_page_default_map_type);

  map.setOptions({
    streetViewControl: false,
    center: {
      lat: mapOptions.lpac_map_order_latitude,
      lng: mapOptions.lpac_map_order_longitude,
    },
    zoom: 16,
    draggableCursor: "default",
    keyboardShortcuts: false,
    gestureHandling: "none",
  });

  const latlng = {
    lat: mapOptions.lpac_map_order_latitude,
    lng: mapOptions.lpac_map_order_longitude,
  };

  const markerOptions = {
    clickable: false,
    position: latlng,
    map: map,
  };

  const marker = initializeMarker(markerOptions);
  const infowindow = initializeInfoWindow();

  // Only open the infowindow if we have a shipping address
  if (mapOptions.lpac_map_order_shipping_address_1) {
    infowindow.setContent(
      `<p> ${mapOptions.lpac_map_order_shipping_address_1} <br/> ${mapOptions.lpac_map_order_shipping_address_2} </p>`
    );
    infowindow.open(map, marker);
  }
}

setupOrderReceivedMap();
