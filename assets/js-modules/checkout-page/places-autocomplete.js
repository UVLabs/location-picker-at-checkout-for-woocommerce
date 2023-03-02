import {
  fillAllAddressFields,
  fillBillingFields,
  fillShippingFields,
  isShippingToDifferentAddress,
} from "./fill-fields.js";
import {
  fillLatLong,
  listenToMapClicks,
  listenToMapDrag,
  setMap,
} from "../set-map.js";
import { getStreetAddress } from "../utils/address-components.js";

/**
 * Places Autocomplete feature.
 *
 * https://developers.google.com/maps/documentation/javascript/examples/places-autocomplete
 *
 * @returns
 */
export function setupPlacesAutoComplete(mapData) {
  const map = mapData.map;
  const mapOptions = mapData.mapOptions;

  const fields = mapOptions.lpac_places_autocomplete_fields;

  // Add autocomplete to searchbox on map if option is enabled.
  if (
    typeof lpac_pro_js !== "undefined" &&
    lpac_pro_js.places_autocomplete_searchbox_on_map !== false
  ) {
    fields.push("lpac-map-searchbox");
  }

  fields.forEach((fieldID) => {
    const field = document.querySelector("#" + fieldID);
    const places_autocomplete_used = document.querySelector(
      "#lpac_places_autocomplete"
    );

    /*
     * If field doesn't exist bail.
     * This might happen if user sets shipping destination to "Force shipping to the customer billing address" so the shipping fields wouldn't exist.
     */
    if (!field) {
      return;
    }

    const options = {
      fields: ["address_components", "formatted_address", "geometry", "name"],
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

    /*
     * Bind the map's bounds (viewport) property to the autocomplete object,
     * so that the autocomplete requests use the current map bounds for the
     * bounds option in the request.
     */
    autoComplete.bindTo("bounds", map);

    autoComplete.addListener("place_changed", () => {
      const results = [autoComplete.getPlace()];

      /**
       * Force street address line 1 to only show street address.
       * See https://stackoverflow.com/a/13147032/4484799
       */
      if ("lpac-map-searchbox" !== fieldID) {
        // Don't dissect map search box (because we want the customer to always know what address they selected)
        field.value = autoComplete.getPlace().name;
        field.addEventListener("blur", function () {
          if (autoComplete.getPlace().name) {
            // Timeoutfunction allows to force the autocomplete field to only display the street name.
            setTimeout(function () {
              if (field.value.length > 0) {
                // Prevent the code from setting the input field to the last selected address, in cases where the customer might have manually changed the autocompleted address.
                return;
              }
              field.value = getStreetAddress(autoComplete.getPlace());
            }, 1);
          }
        });
      }

      const latlng = {
        lat: parseFloat(results[0].geometry.location.lat()),
        lng: parseFloat(results[0].geometry.location.lng()),
      };

      // Add some more data to the object before passing it on
      mapData.latlng = latlng;
      mapData.results = results;

      /**
       * Fill in shipping fields when an address is selected from the autocomplete suggestions dropdown
       */
      if (fieldID.includes("shipping")) {
        if (mapOptions.lpac_places_fill_shipping_fields) {
          fillShippingFields(results);
        }
        fillLatLong(latlng, mapOptions);
        setMap(mapData);
        places_autocomplete_used.value = 1;
        // Add event listeners to map
        listenToMapClicks(mapData);
        listenToMapDrag(mapData);
      }

      /**
       * Fill in billing fields when an address is selected from the autocomplete suggestions dropdown
       */
      if (fieldID.includes("billing")) {
        if (mapOptions.lpac_places_fill_billing_fields) {
          // TODO this condition might be redundant since the plugin is already smart detecting which fields to fill out. And the admin can disable filling of all fields with a filter.
          fillBillingFields(results);
        }

        /**
         * If shipping to a different address, fill in the billing fields  then bail
         * If not shipping to a different address, let plugin update map and cords
         */
        if (true === isShippingToDifferentAddress()) {
          return;
        }

        /**
         * When admin has "force shipping to customer billing address" option enabled, we want to fill in the billing fields.
         */
        if (
          "billing_only" === mapOptions.lpac_wc_shipping_destination_setting
        ) {
          fillBillingFields(results); // this might be redudant because we're always filling in the billing fields
        }

        /**
         * Fluid checkout does things differently.
         *
         * In Fluid Checkout (FC) this checkbox actually means that the customer billing address is the same as their shipping.
         * By default in FC, shipping address area is always present, so in essence;
         * when "Billing TO: Same as shipping address" is unchecked, we should not be updating the map view when those billing fields are updated. But we can update the billing fields themselves.
         */
        if (checkoutProvider && "fluidcheckout" === checkoutProvider) {
          const billingSameAsShippingCheckbox = document.querySelector(
            "#billing_same_as_shipping"
          );

          if (
            billingSameAsShippingCheckbox &&
            billingSameAsShippingCheckbox.checked === false
          ) {
            fillBillingFields(results);
            return;
          }
        }

        fillLatLong(latlng, mapOptions);
        setMap(mapData);
        places_autocomplete_used.value = 1;
        // Add event listeners to map
        listenToMapClicks(mapData);
        listenToMapDrag(mapData);
      }

      /**
       * Support filling in of fields for search box on map feature.
       */
      if (
        fieldID.includes("lpac-map-searchbox") &&
        true === window.lpacCanUsePremiumCode
      ) {
        fillAllAddressFields(results);
        fillLatLong(latlng, mapOptions);
        setMap(mapData);
        places_autocomplete_used.value = 1;
        // Add event listeners to map
        listenToMapClicks(mapData);
        listenToMapDrag(mapData);
      }
    });
  });
}
