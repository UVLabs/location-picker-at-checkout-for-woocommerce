/**
 * Globals:
 *
 * mapOptions, checkoutProvider, lpacLastOrder, storeLocations, lpac_pro_js (available when PRO version active)
 */
/* Get our global map variables from base-map.js */
const map = window.lpac_map;
const marker = window.lpac_marker;
const infowindow = window.lpac_infowindow;

const geocoder = new google.maps.Geocoder();

const find_location_btn = document.querySelector("#lpac-find-location-btn");
const places_autocomplete_used = document.querySelector(
  "#lpac_places_autocomplete"
);

if (typeof find_location_btn !== "undefined" && find_location_btn !== null) {
  find_location_btn.addEventListener("click", () => {
    lpac_bootstrap_map_functionality(geocoder, map, infowindow);
  });
} else {
  console.log("LPAC: Detect location button not present, skipping...");
}

/**
 * Removes the plus code from an address if the option is turned on in the plugin's settings.
 */
function lpacRemovePlusCode(address) {
  if (!mapOptions.lpac_remove_address_plus_code) {
    return address;
  }

  const firstBlock = address.split(" ", 1);

  if (firstBlock[0].includes("+")) {
    address = address.replace(firstBlock[0], "").trim();
  }

  return address;
}

/**
 * Get Lat and Long cords from browser navigator.
 */
function get_navigator_coordinates() {
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
 *  Bootstrap the functionality of the map and marker.
 */
async function lpac_bootstrap_map_functionality(geocoder, map, infowindow) {
  const position = await get_navigator_coordinates();

  if (position) {
    var latitude = position.coords.latitude;
    var longitude = position.coords.longitude;
  } else {
    console.log(
      "Location Picker At Checkout Plugin: Position object is empty. Navigator might be disabled or this site might be detected as insecure."
    );
    var latitude = mapOptions.lpac_map_default_latitude;
    var longitude = mapOptions.lpac_map_default_longitude;
  }

  const latlng = {
    lat: parseFloat(latitude),
    lng: parseFloat(longitude),
  };

  /**
   * We're setting this to '' so that we can force the user to make use of the map if the option is enabled.
   * So that if we do not receive a location, the user can enter one manually.
   */
  const latlngEmpty = {
    lat: "",
    lng: "",
  };

  /**
   * Setup our initial map marker and listening events.
   */
  lpac_setup_initial_map_marker_position(latlng);

  /**
   * Fill in latitude and longitude fields.
   */
  if (position) {
    lpac_fill_in_latlng(latlng);
  } else {
    lpac_fill_in_latlng(latlngEmpty);
  }

  places_autocomplete_used.value = 0;
}

/**
 * Function getting address details from latitude and longitudes.
 */
async function lpac_geocode_coordinates(latlng) {
  var address_array = "";

  await geocoder
    .geocode({ location: latlng }, (results, status) => {
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
      map.panTo(latlng);
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
 * Setup the intial marker location and listening events.
 */
async function lpac_setup_initial_map_marker_position(latlng) {
  const results = await lpac_geocode_coordinates(latlng);

  if (!results[0]) {
    return;
  }

  map.setZoom(16);
  map.setCenter(latlng);

  marker.setPosition(latlng);

  let detected_address = results[0].formatted_address;

  detected_address = lpacRemovePlusCode(detected_address);

  infowindow.setContent(detected_address);
  infowindow.open(map, marker);

  lpac_fill_in_address_fields(results);
  lpac_marker_listen_to_drag();
  lpac_map_listen_to_clicks();
}

/**
 *  Handle clicking of map so marker, fields and coordinates inputs get filled in.
 */
function lpac_map_listen_to_clicks() {
  /**
   * Clear previous event listeners of this type before adding this new one.
   *
   * This is the only function where we're defining this event listener for the map, so it's fine to remove it this way.
   * https://developers.google.com/maps/documentation/javascript/events#removing
   */
  google.maps.event.clearListeners(map, "click");

  map.addListener("click", async function (event) {
    const results = await lpac_geocode_coordinates(event.latLng);

    if (!results[0]) {
      console.log(
        "LPAC: Results not as expected. See lpac_map_listen_to_clicks()"
      );
      return;
    }

    const lat = event.latLng.lat();
    const lng = event.latLng.lng();

    const latLng = {
      lat: parseFloat(lat),
      lng: parseFloat(lng),
    };

    lpac_fill_in_address_fields(results);
    lpac_fill_in_latlng(latLng);

    marker.setPosition(event.latLng);

    let detected_address = results[0].formatted_address;

    detected_address = lpacRemovePlusCode(detected_address);

    infowindow.setContent(detected_address);
    infowindow.open(map, marker);

    places_autocomplete_used.value = 0;
  });
}
window.lpac_map_listen_to_clicks = lpac_map_listen_to_clicks;

/**
 *  Handle dragging of marker so fields and coordinates inputs get filled in.
 */
function lpac_marker_listen_to_drag() {
  /**
   * Clear previous event listeners of this type before adding this new one.
   *
   * This is the only function where we're defining this event listener for the marker, so it's fine to remove it this way.
   * https://developers.google.com/maps/documentation/javascript/events#removing
   */
  google.maps.event.clearListeners(marker, "dragend");

  google.maps.event.addListener(marker, "dragend", async function (event) {
    const moved_to_lat = event.latLng.lat();
    const moved_to_lng = event.latLng.lng();

    const latlng = {
      lat: parseFloat(moved_to_lat),
      lng: parseFloat(moved_to_lng),
    };

    let results = await lpac_geocode_coordinates(latlng);

    if (!results[0]) {
      console.log("Results not as expected. See lpac_marker_listen_to_drag()");
      return;
    }

    let moved_to_address = results[0].formatted_address;

    moved_to_address = lpacRemovePlusCode(moved_to_address);

    infowindow.setContent(moved_to_address);

    lpac_fill_in_address_fields(results);
    lpac_fill_in_latlng(latlng);

    places_autocomplete_used.value = 0;
  });
}
window.lpac_marker_listen_to_drag = lpac_marker_listen_to_drag;

/**
 * Function responsible filling in the latitude and longitude fields.
 */
function lpac_fill_in_latlng(latlng) {
  if (!latlng.lat || !latlng.lng) {
    console.log(
      "Location Picker At Checkout Plugin: Empty latlng. See lpac_fill_in_latlng()"
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

  latitude.value = latlng.lat;
  longitude.value = latlng.lng;

  latitude.dispatchEvent(new Event("input", { bubbles: false }));
  longitude.dispatchEvent(new Event("input", { bubbles: false }));

  if (mapOptions.fill_in_fields === false) {
    /**
     * Ensure that this event is fired and the checkout is updated.
     *
     * If the filter(lpac_fill_checkout_fields) to not fill in address fields is set to true, we need to call this event here so that the cart can update and
     * operations such as the cost by distance feature that require the new Lat and Long can be updated accordingly to show the new price.
     */
    if (jQuery) {
      jQuery(document.body).trigger("update_checkout");
    }
  }
}

/**
 * Function responsible for ochestrating the address filling methods.
 */
function lpac_fill_in_address_fields(results) {
  // Filter to allow users to prevent filling of fields by the map.
  if (mapOptions.fill_in_fields === false) {
    return;
  }

  /** Fluid checkout does things differently **/
  if (checkoutProvider && checkoutProvider === "fluidcheckout") {
    lpac_fill_in_shipping_fields(results);

    const billingSameAsShippingCheckbox = document.querySelector(
      "#billing_same_as_shipping"
    );

    if (
      billingSameAsShippingCheckbox &&
      billingSameAsShippingCheckbox.checked === true
    ) {
      lpac_fill_in_billing_fields(results);
    }

    return;
  }
  /** / */

  const shipToDifferentAddressCheckbox = document.querySelector(
    "#ship-to-different-address-checkbox"
  );

  if (
    shipToDifferentAddressCheckbox &&
    shipToDifferentAddressCheckbox.checked === true
  ) {
    lpac_fill_in_shipping_fields(results);
  } else {
    lpac_fill_in_billing_fields(results);
  }
  /**
   * Ensure that this event is fired and the checkout is updated.
   *
   * In some themes the event does not automatically fire after LPAC updates the address. So here we're ensuring that it does.
   */
  if (jQuery) {
    jQuery(document.body).trigger("update_checkout");
  }
}

/**
 * Fill in all shipping fields.
 *
 * @param {array} results
 */
function lpac_fill_in_shipping_fields(results) {
  lpac_fill_in_shipping_country_region(results);
  lpac_fill_in_shipping_full_address(results);
  lpac_fill_in_shipping_town_city(results);
  lpac_fill_in_shipping_state_county(results);
  lpac_fill_in_shipping_zipcode(results);
}

/**
 * Fill in all Shipping fields for Places autocomplete feature.
 * We are not filling in the full address field because it is pulled from the Places Autocomplete dropdown
 *
 * @param {array} results
 */
function lpacFillPlacesAutocompleteShippingFields(results) {
  lpac_fill_in_shipping_country_region(results);
  lpac_fill_in_shipping_town_city(results);
  lpac_fill_in_shipping_state_county(results);
  lpac_fill_in_shipping_zipcode(results);
  /**
   * Ensure that this event is fired and the checkout is updated.
   *
   * In some themes the event does not automatically fire after LPAC updates the address. So here we're ensuring that it does.
   */
  if (jQuery) {
    jQuery(document.body).trigger("update_checkout");
  }
}

/**
 * Fill in all billing fields.
 *
 * @param {array} results
 */
function lpac_fill_in_billing_fields(results) {
  lpac_fill_in_billing_country_region(results);
  lpac_fill_in_billing_full_address(results);
  lpac_fill_in_billing_town_city(results);
  lpac_fill_in_billing_state_county(results);
  lpac_fill_in_billing_zipcode(results);
}

/**
 * Fill in all billing fields for Places autocomplete feature.
 * We are not filling in the full address field because it is pulled from the Places Autocomplete dropdown
 *
 * @param {array} results
 */
function lpacFillPlacesAutocompleteBillingFields(results) {
  lpac_fill_in_billing_country_region(results);
  lpac_fill_in_billing_town_city(results);
  lpac_fill_in_billing_state_county(results);
  lpac_fill_in_billing_zipcode(results);
  /**
   * Ensure that this event is fired and the checkout is updated.
   *
   * In some themes the event does not automatically fire after LPAC updates the address. So here we're ensuring that it does.
   */
  if (jQuery) {
    jQuery(document.body).trigger("update_checkout");
  }
}

/*
 *  Get country from map.
 */
function lpac_get_country(results) {
  if (!results[0]) {
    return;
  }

  var country = "";
  const country_array = results[0].address_components.find(
    (addr) => addr.types[0] === "country"
  );

  if (country_array) {
    country = country_array.short_name;
  }

  return country;
}

/*
 *  Get full formatted address
 */
function lpac_get_full_address(results) {
  if (!results[0]) {
    return;
  }

  let full_address = results[0].formatted_address;

  full_address = lpacRemovePlusCode(full_address);

  return full_address;
}

/*
 *  Get Town/City
 */
function lpac_get_town_city(results) {
  if (!results[0]) {
    return;
  }

  var town_city = "";
  const town_city_array = results[0].address_components.find(
    (addr) => addr.types[0] === "locality"
  );
  const town_city_array2 = results[0].address_components.find(
    (addr) => addr.types[0] === "postal_town"
  );

  /*
   * Locality "locality" is used because its most commonly available.
   */
  if (town_city_array) {
    town_city = town_city_array.long_name;
  }

  /*
   * But we override Locality with the more standard "postal_town" field if it exists.
   */
  if (town_city_array2) {
    town_city = town_city_array2.long_name;
  }

  return town_city;
}

/*
 *  Get State/County
 */
function lpac_get_state_county(results) {
  if (!results[0]) {
    return;
  }

  let address_component = "";

  for (let address_component of results[0].address_components) {
    for (type of address_component.types) {
      if (type === "administrative_area_level_1") {
        return address_component;
      }
    }
  }

  return address_component;
}

/*
 *  Get State/County
 */
function lpac_get_zip_code(results) {
  if (!results[0]) {
    return;
  }

  var zipcode = "";
  const zipcode_array = results[0].address_components.find(
    (addr) => addr.types[0] === "postal_code"
  );

  if (zipcode_array) {
    zipcode = zipcode_array.short_name;
  }

  return zipcode;
}

/*
 *  Fill in shipping country field
 */
function lpac_fill_in_shipping_country_region(results) {
  const shipping_country = document.querySelector("#shipping_country");

  if (typeof shipping_country === "undefined" || shipping_country === null) {
    return;
  }

  shipping_country.value = lpac_get_country(results);

  shipping_country.dispatchEvent(new Event("change", { bubbles: true })); // ensure Select2 sees the change
}

/*
 *  Fill in billing country field
 */
function lpac_fill_in_billing_country_region(results) {
  const billing_country = document.querySelector("#billing_country");

  if (typeof billing_country === "undefined" || billing_country === null) {
    return;
  }

  billing_country.value = lpac_get_country(results);
  billing_country.dispatchEvent(new Event("change", { bubbles: true })); // ensure Select2 sees the change
}

/*
 *  Fill in shipping street address field
 */
function lpac_fill_in_shipping_full_address(results) {
  const full_shipping_address = document.querySelector("#shipping_address_1");

  if (
    typeof full_shipping_address === "undefined" ||
    full_shipping_address === null
  ) {
    return;
  }

  full_shipping_address.value = lpac_get_full_address(results);
}

/*
 *  Fill in billing street address field
 */
function lpac_fill_in_billing_full_address(results) {
  const full_billing_address = document.querySelector("#billing_address_1");

  if (
    typeof full_billing_address === "undefined" ||
    full_billing_address === null
  ) {
    return;
  }

  full_billing_address.value = lpac_get_full_address(results);
}

/*
 *  Fill in shipping Town/City field
 */
function lpac_fill_in_shipping_town_city(results) {
  const shipping_city = document.querySelector("#shipping_city");

  if (typeof shipping_city === "undefined" || shipping_city === null) {
    return;
  }

  shipping_city.value = lpac_get_town_city(results);
}

/*
 *  Fill in billing Town/City field
 */
function lpac_fill_in_billing_town_city(results) {
  const billing_city = document.querySelector("#billing_city");

  if (typeof billing_city === "undefined" || billing_city === null) {
    return;
  }

  billing_city.value = lpac_get_town_city(results);
}

/*
 *  Fill in shipping State/County field
 */
function lpac_fill_in_shipping_state_county(results) {
  /*
   * If we have values in our lpac_get_state_county() function
   */
  if (lpac_get_state_county(results)) {
    /*
     * This field changes based on the country.
     * For some countries WC shows a text input and others it shows a dropdown
     * We need to get the field everytime or risk JS not being able to set it.
     */
    const shipping_state_field = document.querySelector("#shipping_state");

    if (
      typeof shipping_state_field === "undefined" ||
      shipping_state_field === null
    ) {
      return;
    }

    if (shipping_state_field.classList.contains("select2-hidden-accessible")) {
      shipping_state_field.value = lpac_get_state_county(results).short_name;

      shipping_state_field.dispatchEvent(
        new Event("change", { bubbles: true })
      ); // ensure Select2 sees the change
    } else {
      shipping_state_field.value = lpac_get_state_county(results).long_name;
    }
  }
}

/*
 *  Fill in billing State/County field
 */
function lpac_fill_in_billing_state_county(results) {
  if (lpac_get_state_county(results)) {
    /*
     * This field changes based on the country.
     * For some countries WC shows a text input and others it shows a dropdown
     * We need to get the field everytime or risk JS not being able to set it.
     */
    const billing_state_field = document.querySelector("#billing_state");

    if (
      typeof billing_state_field === "undefined" ||
      billing_state_field === null
    ) {
      return;
    }

    if (billing_state_field.classList.contains("select2-hidden-accessible")) {
      billing_state_field.value = lpac_get_state_county(results).short_name;
      billing_state_field.dispatchEvent(new Event("change", { bubbles: true })); // ensure Select2 sees the change
    } else {
      billing_state_field.value = lpac_get_state_county(results).long_name;
    }
  }
}

/*
 *  Fill in shipping Zipcode field
 */
function lpac_fill_in_shipping_zipcode(results) {
  const shipping_zipcode = document.querySelector("#shipping_postcode");

  if (typeof shipping_zipcode === "undefined" || shipping_zipcode === null) {
    return;
  }

  shipping_zipcode.value = lpac_get_zip_code(results);
}

/*
 *  Fill in billing Zipcode field
 */
function lpac_fill_in_billing_zipcode(results) {
  const billing_zipcode = document.querySelector("#billing_postcode");

  if (typeof billing_zipcode === "undefined" || billing_zipcode === null) {
    return;
  }

  billing_zipcode.value = lpac_get_zip_code(results);
}

/**
 * Show or hide the map.
 *
 * Fire custom events based on the state of the map visibility.
 */
function changeMapVisibility(show) {
  // console.log('show', show);

  const changedEventBefore = new Event("custom:lpacMapVisibilityCheckedBefore");
  document.dispatchEvent(changedEventBefore);

  const storeSelector = document.querySelector(
    "#lpac_order__origin_store_field"
  );

  const saveAddressCheckbox = document.querySelector(
    "#lpac_save_address_checkbox_field"
  );

  if (show) {
    document.querySelector("#lpac-map-container").style.display = "block";
    document.querySelector("#lpac_is_map_shown").value = 1;

    if (storeSelector) {
      storeSelector.style.display = "block";
    }

    if (saveAddressCheckbox) {
      saveAddressCheckbox.style.display = "block";
    }

    const showEvent = new Event("custom:lpacMapVisibilityShow");
    document.dispatchEvent(showEvent);
  } else {
    document.querySelector("#lpac-map-container").style.display = "none";
    document.querySelector("#lpac_is_map_shown").value = 0;

    if (storeSelector) {
      storeSelector.style.display = "none";
    }

    if (saveAddressCheckbox) {
      saveAddressCheckbox.style.display = "none";
    }

    const hideEvent = new Event("custom:lpacMapVisibilityHide");
    document.dispatchEvent(hideEvent);
  }

  const changedEventAfter = new Event("custom:lpacMapVisibilityCheckedAfter");
  document.dispatchEvent(changedEventAfter);
}

/**
 * Ajax call to determine when the map should be shown or hidden.
 *
 * See Lpac\Controllers::Map_Visibility_Controller
 */
function lpacHideShowMap() {
  wp.ajax
    .post("lpac_checkout_map_visibility", {})
    .done(function (response) {
      const show = Boolean(response);
      changeMapVisibility(show);
    })
    .fail(function (response) {
      console.log(response);
    });
}

/**
 * Fill in coordinatee fields for last order.
 */
function lpacSetLastOrderLocationCords() {
  if (typeof lpacLastOrder === "undefined" || lpacLastOrder === null) {
    return;
  }

  // If no coordinates exist don't try to plot the location on the map
  if (!lpacLastOrder.latitude || !lpacLastOrder.longitude) {
    return;
  }

  const latlng = {
    lat: parseFloat(lpacLastOrder.latitude),
    lng: parseFloat(lpacLastOrder.longitude),
  };

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

  // Set the checkout fields lat and long value
  lpac_fill_in_latlng(latlng);
}

/**
 * Make store locator always visible when using autocomplete feature with map turned off.
 *
 * By default we're hiding the store location selector when showing the map because we first want the location cords fields
 * to be filled in before showing the selector (in the case a user has never ordered from site before).  But when the autocomplete feature is being used without the map we want the field always visible
 */
function lpacSetLastOrderForAutocompleteWithoutMap() {
  const hideMapForAutocomplete = mapOptions.lpac_places_autocomplete_hide_map;
  const enablePlacesAutoComplete = mapOptions.lpac_enable_places_autocomplete;

  if (enablePlacesAutoComplete === false || hideMapForAutocomplete === false) {
    return;
  }

  const field = document.querySelector("#lpac_order__origin_store_field");
  if (field) {
    field.classList.remove("hidden");
  }

  lpacSetLastOrderLocationCords();
}

/**
 * Set the previous order marker.
 */
function lpacSetLastOrderMarker() {
  if (lpacLastOrder === null) {
    return;
  }

  // Wait for map to load then add our marker
  google.maps.event.addListenerOnce(map, "tilesloaded", function () {
    lpacSetLastOrderLocationCords();

    const latlng = {
      lat: parseFloat(lpacLastOrder.latitude),
      lng: parseFloat(lpacLastOrder.longitude),
    };

    marker.setPosition(latlng);

    // Only open the infowindow if we have a shipping address from the last order.
    if (lpacLastOrder.address) {
      infowindow.setContent(lpacLastOrder.address);
      /**
       * Check if plotting is complete before opening the info window.
       * This is because everytime an infowindow opens it focuses the map view on that infowindow.
       * This can cause the map to pan to the info window of the last plotted region instead of the last order location.
       * So here we're making sure that plotting all regions is complete(if option is turned on) and then opening the last order details infowindow.
       */
      if (
        typeof lpac_pro_js !== "undefined" &&
        lpac_pro_js !== null &&
        lpac_pro_js.shippingRegions.enabled &&
        lpac_pro_js.shippingRegions.showShippingRegions
      ) {
        var intrval = setInterval(function () {
          if (typeof window.lpacRegionsPlottingComplete !== "undefined") {
            infowindow.open(map, marker);
            map.setCenter(latlng);
            clearInterval(intrval);
          }
        }, 100);
      } else {
        infowindow.open(map, marker);
        map.setCenter(latlng);
      }
    } else {
      infowindow.setContent(lpacTranslatedJsStrings.generic_last_order_address);
      infowindow.open(map, marker);
      map.setCenter(latlng);
    }

    map.setZoom(16);

    lpac_marker_listen_to_drag();
    lpac_map_listen_to_clicks();
  });
}

/**
 * Places Autocomplete feature.
 *
 * https://developers.google.com/maps/documentation/javascript/examples/places-autocomplete
 *
 * @returns
 */
function addPlacesAutoComplete() {
  //TODO Update this method to remove the heavy logic for checking whether shipping to billing address or shipping address is checked.
  // We can simply check if the option exists versus running all the logic to determine the shipping destination.
  // See moveStoreSelector()
  if (typeof mapOptions === "undefined" || mapOptions === null) {
    console.log(
      "LPAC: mapOptions object not present. This shouldn't be happening here. Contact Support."
    );
    return;
  }

  // Return if feature not enabled
  if (!mapOptions.lpac_enable_places_autocomplete) {
    return;
  }

  // Hide map if option is turned on.
  if (mapOptions.lpac_places_autocomplete_hide_map) {
    changeMapVisibility(false);
  }

  const fields = mapOptions.lpac_places_autocomplete_fields;

  fields.forEach((fieldID) => {
    const field = document.querySelector("#" + fieldID);

    /*
     * If field doesn't exist bail.
     * This might happen if user sets shipping destination to "Force shipping to the customer billing address" so the shipping fields wouldn't exist.
     */
    if (!field) {
      return;
    }

    const options = {
      fields: ["address_components", "formatted_address", "geometry"],
      types: ["address"],
    };

    /*
     * Add Places Autocomplete restrictions set in PRO plugin settings
     * lpac_pro_js is in global scope
     */
    if (typeof lpac_pro_js !== "undefined" && lpac_pro_js !== null) {
      if (lpac_pro_js.places_autocomplete_restrictions.length > 0) {
        options.componentRestrictions = {
          country: lpac_pro_js.places_autocomplete_restrictions,
        };
      }

      options.types = lpac_pro_js.places_autocomplete_type;
    }

    const autoComplete = new google.maps.places.Autocomplete(field, options);

    /* Bind the map's bounds (viewport) property to the autocomplete object,
		so that the autocomplete requests use the current map bounds for the
		bounds option in the request. */
    autoComplete.bindTo("bounds", map);

    autoComplete.addListener("place_changed", () => {
      const results = [autoComplete.getPlace()];

      const latlng = {
        lat: parseFloat(results[0].geometry.location.lat()),
        lng: parseFloat(results[0].geometry.location.lng()),
      };

      if (fieldID.includes("shipping")) {
        if (mapOptions.lpac_places_fill_shipping_fields) {
          lpacFillPlacesAutocompleteShippingFields(results);
        }

        lpac_fill_in_latlng(latlng);

        map.setCenter(latlng);
        marker.setPosition(latlng);
        map.setZoom(16);
        infowindow.setContent(results[0].formatted_address);
        infowindow.open(map, marker);
        places_autocomplete_used.value = 1;
        // Add event listeners to map
        lpac_marker_listen_to_drag();
        lpac_map_listen_to_clicks();
      } else {
        if (mapOptions.lpac_places_fill_billing_fields) {
          lpacFillPlacesAutocompleteBillingFields(results);
        }

        let shipToDifferentAddress = false;

        const shipToDifferentAddressCheckbox = document.querySelector(
          "#ship-to-different-address-checkbox"
        );

        if (
          shipToDifferentAddressCheckbox &&
          shipToDifferentAddressCheckbox.checked === true
        ) {
          shipToDifferentAddress = true;
        }

        /** Fluid checkout does things differently **/
        if (checkoutProvider && checkoutProvider === "fluidcheckout") {
          const billingSameAsShippingCheckbox = document.querySelector(
            "#billing_same_as_shipping"
          );

          if (
            billingSameAsShippingCheckbox &&
            billingSameAsShippingCheckbox.checked === true
          ) {
            /**
             * In Fluid Checkout (FC) this checkbox actually means that the customer billing address is the same as their shipping.
             * By default in FC, shipping address is always present, so in essence, when "Billing TO: Same as shipping address" is unchecked, we should not be updating the map view when those billing fields are updated.
             */
            shipToDifferentAddress = true;
          }
        }
        /** / */

        /*
         * When Shipping destination is set as "Force shipping to the customer billing address" or " Default to customer billing address" in WooCommerce->Shipping->Shipping Options
         * We would want to adjust the map as needed.
         *
         * Also check the status of shipping to a different address checkbox. Based on it's value we'd want to decide whether to update the map view or not.
         */
        if (
          (mapOptions.lpac_wc_shipping_destination_setting === "billing_only" ||
            mapOptions.lpac_wc_shipping_destination_setting === "billing" ||
            (fields.length === 1 && fields.includes("billing_address_1"))) &&
          shipToDifferentAddress === false
        ) {
          lpac_fill_in_latlng(latlng);
          map.setCenter(latlng);
          marker.setPosition(latlng);
          map.setZoom(16);
          infowindow.setContent(results[0].formatted_address);
          infowindow.open(map, marker);
          places_autocomplete_used.value = 1;
          // Add event listeners to map
          lpac_marker_listen_to_drag();
          lpac_map_listen_to_clicks();
        }
      }
    });
  });
}
addPlacesAutoComplete();

/**
 * Detect when shipping methods are changed based on WC custom updated_checkout event.
 * This event can't be accessed via vanilla JS because it's triggered by jQuery.
 */
(function ($) {
  "use strict";

  $(document).ready(function () {
    // Prevents ajax call in lpacHideShowMap from overriding our lpac_places_autocomplete_hide_map option.
    if (!mapOptions.lpac_places_autocomplete_hide_map) {
      $(document.body).on("updated_checkout", lpacHideShowMap);
    }

    // Fluid checkout updates frequently so always check for fields after checkout updating event is fired.
    // https://github.com/fluidweb-co/fluid-checkout/issues/47#issuecomment-1109699262
    if (checkoutProvider && checkoutProvider === "fluidcheckout") {
      $(document.body).on("updated_checkout", () => {
        addPlacesAutoComplete();
      });
    }

    /**
     * If the auto detect location feature is turned on, then detect the location but don't output the last order details.
     * Do this only when we don't have a last order location.
     */
    if (
      mapOptions.lpac_auto_detect_location &&
      (typeof lpacLastOrder === "undefined" || lpacLastOrder === null)
    ) {
      lpac_bootstrap_map_functionality(geocoder, map, infowindow);
    } else {
      lpacSetLastOrderMarker();
      lpacSetLastOrderForAutocompleteWithoutMap();
    }

    /**
     * Move the store locator selector based on whether shipping to billing or shipping address.
     *
     * This only runs when the map is not being displayed.
     *
     * @returns
     */
    function moveStoreSelector() {
      /**
       * We only need to do this when the map isnt present because it would look weird to just have the field appear
       * in only locations where the map can appear.
       */
      const hideMapForAutocomplete =
        mapOptions.lpac_places_autocomplete_hide_map;
      const enablePlacesAutoComplete =
        mapOptions.lpac_enable_places_autocomplete;

      if (hideMapForAutocomplete === false) {
        return;
      }

      let field = $("#lpac_order__origin_store_field");

      let billingAddress =
        $("#billing_address_2_field").length > 0
          ? $("#billing_address_2_field")
          : $("#billing_address_1_field"); // Some checkouts might remove this field or a user might remove it, so we're handling this here.
      let shippingAddress =
        $("#shipping_address_2_field").length > 0
          ? $("#shipping_address_2_field")
          : $("#shipping_address_1_field");

      let shippingToDifferentAddress = "";

      switch (checkoutProvider) {
        case "woofunnels":
          shippingToDifferentAddress = $("#shipping_same_as_billing");
          break;
        default:
          shippingToDifferentAddress = $("#ship-to-different-address-checkbox");
          break;
      }

      if (!shippingToDifferentAddress.length) {
        return;
      }

      const shippingToDifferentAddressChecked =
        shippingToDifferentAddress.is(":checked");

      if (checkoutProvider !== "fluidcheckout") {
        // If shipping to billing address keep in billing area
        if (!shippingToDifferentAddressChecked) {
          field.insertAfter(billingAddress);
        } else {
          field.insertAfter(shippingAddress);
        }
      }

      shippingToDifferentAddress.on("click", function () {
        let shippingToDifferentAddress = "";

        switch (checkoutProvider) {
          case "woofunnels":
            shippingToDifferentAddress = $("#shipping_same_as_billing");
            break;
          default:
            shippingToDifferentAddress = $(
              "#ship-to-different-address-checkbox"
            );
            break;
        }

        const shippingToDifferentAddressChecked =
          shippingToDifferentAddress.is(":checked");

        if (!shippingToDifferentAddressChecked) {
          field.insertAfter(billingAddress);
        } else {
          field.insertAfter(shippingAddress);
        }
      });

      /**
       * Weird bug with fluid checkout causing field to disappear if we try to move it too early.
       */
      if (checkoutProvider === "fluidcheckout") {
        $(document.body).on("updated_checkout", function () {
          let field = $("#lpac_order__origin_store_field");
          field.insertAfter("#shipping_address_1_field");
        });
      }
    }
    moveStoreSelector();

    /**
     * Show the store selector field only when the lat and long fields are filled in.
     */
    function showStoreSelector() {
      // Always show the store location when the user is only using the autocomplete feature and they are hiding the map.
      // In those cases we have no need to wait until a location is found from the map to show the field.
      const hideMapForAutocomplete =
        mapOptions.lpac_places_autocomplete_hide_map;
      const enablePlacesAutoComplete =
        mapOptions.lpac_enable_places_autocomplete;

      if (
        enablePlacesAutoComplete === true &&
        hideMapForAutocomplete === true
      ) {
        return;
      }

      const field = $("#lpac_order__origin_store_field");

      // Hide the field then remove the hidden class so that the slide down effect can work seamlessly.
      field.hide().removeClass("hidden");

      const lat = $("#lpac_latitude");

      lat.on("input", function () {
        field.slideDown();
      });
    }
    showStoreSelector();

    /**
     * Set the store location selector to the last selected store.
     */
    function setLastSelectedStore() {
      const field = $("#lpac_order__origin_store");

      if (field.length < 1) {
        return;
      }

      const storeLocations = $.map(
        $("#lpac_order__origin_store option"),
        function (el) {
          return el.value;
        }
      );

      const isLoggedIn = $("body").hasClass("logged-in");

      /**
       * For guest checkout we're storing the preferred store location in localStorage
       * For logged in users we're replacing the store_origin_id with the one selected from the shortcode if it's been used, see Lpac\Controllers\Checkout_Page_Controller::get_last_order_location()
       */
      if (isLoggedIn === true) {
        // This might be null if the user has never ordered from this site before.
        if (lpacLastOrder === null) {
          return;
        }

        /**
         * Set the currently selected option to be the last order's selected option.
         *
         * If that store location no longer exists, then set the option to the default empty option.
         */
        if (
          lpacLastOrder.store_origin_id.length > 0 &&
          storeLocations.indexOf(lpacLastOrder.store_origin_id) > -1
        ) {
          field.val(lpacLastOrder.store_origin_id).change();
        }
      } else {
        const preferredOriginStore = localStorage.getItem(
          "lpac_user_preferred_store_location_id"
        );

        if (!preferredOriginStore) {
          return;
        }

        field.val(preferredOriginStore).change();
      }
    }
    setLastSelectedStore();

    /**
     * Detect when the store origin location is changed
     */
    function detectOriginStoreChange() {
      const field = $("#lpac_order__origin_store");

      if (field.length < 1) {
        return;
      }

      field.on("change", function () {
        $(document.body).trigger("update_checkout");
      });
    }
    detectOriginStoreChange();

    /**
     * Set store locations markers on checkout map.
     */
    function lpacSetStoreLocationsMarkers() {
      google.maps.event.addListenerOnce(map, "tilesloaded", function () {
        if (
          typeof storeLocations === "undefined" ||
          storeLocations === null ||
          !storeLocations.length > 0
        ) {
          return;
        }

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

          const marker = new google.maps.Marker({
            clickable: false,
            icon:
              typeof lpac_pro_js !== "undefined" && lpac_pro_js.is_pro
                ? location.store_icon_text
                : "", // show icon only in pro
            position: latlng,
            map: map,
          });

          const infoWindow = new google.maps.InfoWindow({
            content: location.store_name_text,
            disableAutoPan: true,
          });

          infoWindow.open(map, marker);
        });
      });
    }
    lpacSetStoreLocationsMarkers();
  });
})(jQuery);
