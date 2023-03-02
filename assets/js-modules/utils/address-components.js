/*
 * Get country from map.
 */
export function getCountry(results) {
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

// ----- Address Dissecting (Remove from main map file when modules implemented ) ----- //

/**
 * Removes the plus code from an address if the option is turned on in the plugin's settings.
 */
export function removePlusCode(address) {
  if (!mapOptions.lpac_remove_address_plus_code) {
    return address;
  }

  const firstBlock = address.split(" ", 1);

  if (firstBlock[0].includes("+")) {
    address = address.replace(firstBlock[0], "").trim();
  }

  return address;
}

/*
 * Get full formatted address
 */
function getFullAddress(results) {
  if (!results[0]) {
    return;
  }

  let full_address = results[0].formatted_address;

  full_address = removePlusCode(full_address);

  return full_address;
}

// ----- Address Dissecting ----- //

/**
 * Get the street address.
 *
 * @param {object} results
 * @returns null|string
 */
export function getStreetAddress(results) {
  if (!results[0]) {
    return;
  }

  if (!mapOptions.dissect_customer_address) {
    return getFullAddress(results);
  }

  /**
   *  In cases where autocomplete was used, a 'name' property would exist, so lets use that.
   */
  if (results[0].hasOwnProperty("name")) {
    return results[0].name;
  }
  /**
   *  /// END
   */

  const full_address = results[0].formatted_address;

  let streetAddress = "";

  // If option to remove plus code is turned on then there's less work to do.
  if (mapOptions.lpac_remove_address_plus_code) {
    const address = removePlusCode(full_address);
    const parts = address.split(",");
    streetAddress = parts[0].trim();
  } else {
    const blocks = full_address.split(" ", 1);
    const parts = full_address.split(",");

    /**
     * Check if the first block of the address resembles a plus code.
     * If it does then use the second block/part of the address as the street address.
     * If it doesn't then use the first part.
     */
    if (blocks[0].includes("+")) {
      const withoutPlusCode = full_address.replace(blocks[0], "");
      streetAddress = withoutPlusCode.split(",", 1)[0].trim();
    } else {
      streetAddress = parts[0].trim();
    }
  }

  return streetAddress;
}

/*
 * Get Town/City
 */
export function getTownCity(results) {
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
 * Get State/County
 */
export function getStateCounty(results) {
  if (!results[0]) {
    return;
  }

  let address_component = "";

  for (let address_component of results[0].address_components) {
    for (let type of address_component.types) {
      if (type === "administrative_area_level_1") {
        return address_component;
      }
    }
  }

  return address_component;
}

/*
 * Get State/County
 */
export function getZipCode(results) {
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
