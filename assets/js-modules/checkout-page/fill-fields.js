// Globals: mapOptions, checkoutProvider
import * as addressComponent from "../utils/address-components.js";

// --- Delete all original functions once we're done implementing these modules.

export function isShippingToDifferentAddress() {
  const shipToDifferentAddressCheckbox = document.querySelector(
    "#ship-to-different-address-checkbox"
  );

  if (
    shipToDifferentAddressCheckbox &&
    shipToDifferentAddressCheckbox.checked === true
  ) {
    return true;
  }

  return false;
}

/**
 * Fill all our available address fields.
 *
 * @param {object} results The geocoded results from lat and long coordinates.
 */
export function fillAllAddressFields(results) {
  // Filter to allow users to prevent filling of fields by the map.
  if (mapOptions.fill_in_fields === false) {
    return;
  }

  /**
   * Fluid checkout does things differently.
   *
   * In Fluid Checkout (FC) this checkbox actually means that the customer billing address is the same as their shipping.
   * By default in FC, shipping address area is always present, so in essence;
   * when "Billing TO: Same as shipping address" is unchecked, we should not be updating the map view when those billing fields are updated. But we can update the billing fields themselves.
   */
  if (checkoutProvider && "fluidcheckout" === checkoutProvider) {
    fillShippingFields(results);

    const billingSameAsShippingCheckbox = document.querySelector(
      "#billing_same_as_shipping"
    );

    if (
      billingSameAsShippingCheckbox &&
      billingSameAsShippingCheckbox.checked === false // try changing this to true if something is up with FC logic
    ) {
      fillBillingFields(results);
    }

    return;
  }

  if (true === isShippingToDifferentAddress()) {
    fillShippingFields(results);
    return;
  }

  fillBillingFields(results);
}

export function fillShippingFields(results) {
  // Filter to allow users to prevent filling of fields by the map.
  if (mapOptions.fill_in_fields === false) {
    return;
  }

  fillStreetAddress1(results, "shipping");
  fillCountryRegion(results, "shipping");
  fillTownCity(results, "shipping");
  fillStateCounty(results, "shipping");
  fillZipcode(results, "shipping");

  /**
   * Ensure that this event is fired and the checkout is updated.
   *
   * In some themes the event does not automatically fire after LPAC updates the address. So here we're ensuring that it does.
   */
  if (jQuery) {
    jQuery(document.body).trigger("update_checkout");
  }
}

export function fillBillingFields(results) {
  // Filter to allow users to prevent filling of fields by the map.
  if (mapOptions.fill_in_fields === false) {
    return;
  }

  fillStreetAddress1(results, "billing");
  fillCountryRegion(results, "billing");
  fillTownCity(results, "billing");
  fillStateCounty(results, "billing");
  fillZipcode(results, "billing");

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
 * Fill in shipping country field
 */
function fillCountryRegion(results, type) {
  const country = document.querySelector(`#${type}_country`);

  if (typeof country === "undefined" || country === null) {
    return;
  }

  country.value = addressComponent.getCountry(results);

  country.dispatchEvent(new Event("change", { bubbles: true })); // ensure Select2 sees the change
}

/*
 * Fill in shipping street address field
 */
export function fillStreetAddress1(results, type) {
  const street_address_field = document.querySelector(`#${type}_address_1`);

  if (
    typeof street_address_field === "undefined" ||
    street_address_field === null
  ) {
    return;
  }

  street_address_field.value = addressComponent.getStreetAddress(results);
}

/*
 * Fill in shipping Town/City field
 */
function fillTownCity(results, type) {
  const city = document.querySelector(`#${type}_city`);

  if (typeof city === "undefined" || city === null) {
    return;
  }

  city.value = addressComponent.getTownCity(results);
}

/*
 * Fill in shipping State/County field
 */
function fillStateCounty(results, type) {
  /*
   * If we have values in our getStateCounty() function
   */
  if (addressComponent.getStateCounty(results)) {
    /*
     * This field changes based on the country.
     * For some countries WC shows a text input and others it shows a dropdown
     * We need to get the field everytime or risk JS not being able to set it.
     */
    const state_field = document.querySelector(`#${type}_state`);

    if (typeof state_field === "undefined" || state_field === null) {
      return;
    }

    if (state_field.classList.contains("select2-hidden-accessible")) {
      state_field.value = addressComponent.getStateCounty(results).short_name;

      state_field.dispatchEvent(new Event("change", { bubbles: true })); // ensure Select2 sees the change
    } else {
      state_field.value = addressComponent.getStateCounty(results).long_name;
    }
  }
}

/*
 * Fill in shipping Zipcode field
 */
function fillZipcode(results, type) {
  const zipcode = document.querySelector(`#${type}_postcode`);

  if (typeof zipcode === "undefined" || zipcode === null) {
    return;
  }

  zipcode.value = addressComponent.getZipCode(results);
}
