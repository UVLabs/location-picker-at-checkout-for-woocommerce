/**
 * Globals:
 *
 * mapOptions, checkoutProvider, lpacLastOrder, storeLocations, lpac_pro_js (available when PRO version active)
 */
/* Get our global map variables from base-map.js */

import { fillAllAddressFields } from "../../../js-modules/checkout-page/fill-fields.js";
import { setupPlacesAutoComplete } from "../../../js-modules/checkout-page/places-autocomplete.js";
import {
  bootstrapMapFunctionality,
  bootstrapMapFunctionalityJQuery,
  fillLatLong,
  geocodeCoordinates,
  listenToMapClicks,
  listenToMapDrag,
  setupMap,
} from "../../../js-modules/set-map.js";
import { plotStoreLocations } from "../../../js-modules/utils/store-locations.js";

const map = window.lpac_map;
map.setMapTypeId(mapOptions.lpac_checkout_page_map_default_type);

const marker = window.lpac_marker;
const infowindow = window.lpac_infowindow;

const find_location_btn = document.querySelector("#lpac-find-location-btn");
const places_autocomplete_used = document.querySelector(
  "#lpac_places_autocomplete"
);

if (typeof find_location_btn !== "undefined" && find_location_btn !== null) {
  find_location_btn.addEventListener("click", async () => {
    const latLng = await bootstrapMapFunctionality(mapOptions);

    if (latLng.lat !== "" && latLng.lng !== "") {
      const geocodeResults = await geocodeCoordinates(latLng, map);
      const mapData = {
        map,
        mapOptions,
        marker,
        latLng,
        infowindow,
        geocodeResults,
      };
      setupMap(mapData);
      fillAllAddressFields(geocodeResults);
    }

    fillLatLong(latLng, mapOptions);
    places_autocomplete_used.value = 0;
  });
} else {
  console.log("LPAC: Detect location button not present, skipping...");
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
 * Fill in coordinate fields for last order.
 */
function lpacSetLastOrderLocationDetails() {
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

  const latitude = document.querySelector("#lpac_latitude");
  const longitude = document.querySelector("#lpac_longitude");
  const places_autocomplete_used = document.querySelector(
    "#lpac_places_autocomplete"
  );

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

  fillLatLong(latlng, mapOptions);

  // Set the last order value for the places autocomplete field
  places_autocomplete_used.value = lpacLastOrder.used_places_autocomplete;
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

  lpacSetLastOrderLocationDetails();
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
    lpacSetLastOrderLocationDetails();

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
      // TODO, theres no need for this anymore since theres a setting that can just prevent the panning of infowindow.
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

    const mapData = {
      map,
      mapOptions,
      marker,
      infowindow,
    };
    listenToMapDrag(mapData);
    listenToMapClicks(mapData);
  });
}

/**
 * Add Places AutoComplete
 */
function addPlacesAutoComplete() {
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

  // Create our map data that will be passed to setupPlacesAutoComplete() and further passed to setMap()
  const mapData = {
    map,
    marker,
    infowindow,
    mapOptions,
  };

  setupPlacesAutoComplete(mapData);
}

/**
 * Detect when shipping methods are changed based on WC custom updated_checkout event.
 * This event can't be accessed via vanilla JS because it's triggered by jQuery.
 */
(function ($) {
  "use strict";

  $(document).ready(function () {
    // Lets always call the update process on load to clear any stray pieces of pricing data as a caution.
    if (jQuery) {
      jQuery(document.body).trigger("update_checkout");
    }

    // Initialize Places autocomplete
    addPlacesAutoComplete();

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
      const mapData = {
        map,
        mapOptions,
        marker,
        infowindow,
      };
      bootstrapMapFunctionalityJQuery(mapData);
      places_autocomplete_used.value = 0;
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
        case "funnelkit":
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
          case "funnelkit":
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
       * For logged in users we're replacing the store_origin_id with the one selected from the shortcode if it's been used, see Lpac\Controllers\Checkout_Page\Controller::get_last_order_details()
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
      if (
        typeof storeLocations === "undefined" ||
        storeLocations === null ||
        !storeLocations.length > 0
      ) {
        return;
      }

      google.maps.event.addListenerOnce(map, "tilesloaded", function () {
        plotStoreLocations(map, storeLocations);
      });
    }
    lpacSetStoreLocationsMarkers();
  });
})(jQuery);
