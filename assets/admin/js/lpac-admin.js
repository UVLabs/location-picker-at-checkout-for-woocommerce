(function ($) {
  "use strict";

  /**
   * All of the code for your admin-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */

  $(function () {
    $("table#lpac-rules tbody").sortable({
      update: function (event, ui) {
        $("#lpac-rules-saving-success").hide();
        $("#lpac-rules-saving-failed").hide();
        $("#lpac-rules-saving").show();
        const order = $(this).sortable("toArray", { attribute: "data-id" });

        wp.ajax
          .post("lpac_map_visibility_rules_order", { rulesOrder: order })
          .done(function (response) {
            $("#lpac-rules-saving").hide();
            $("#lpac-rules-saving-failed").hide();
            $("#lpac-rules-saving-success").show().delay(1000).fadeOut("slow");

            // console.log(response);
          })
          .fail(function (response) {
            $("#lpac-rules-saving").hide();
            $("#lpac-rules-saving-success").hide();
            $("#lpac-rules-saving-failed").show();
            console.error(response.responseJSON.data);
          });
      },
    });

    /**
     * Hide the save button on the Export tab.
     */
    function hideSaveButtonOnExportTab() {
      const queryString = window.location.search;
      const urlParams = new URLSearchParams(queryString);
      if (urlParams.get("section") === "export") {
        $(".submit").hide();
      }
    }
    // hideSaveButtonOnExportTab();

    /**
     * Check if the Google Maps API Key has been entered on the settings page.
     */
    function checkAPIKeyPresence() {
      const field = $("#lpac_google_maps_api_key");

      if (field.length < 1) {
        return;
      }

      if (field.val().length > 10) {
        return;
      }

      field.css("box-shadow", "1px 1px 10px 5px red");
    }
    checkAPIKeyPresence();

    function toggleField(fieldName) {
      // Update to handle array of field names as well.

      const field = $(fieldName);

      if (!field) {
        return;
      }

      field.closest("tr").toggle();
    }

    /**
     * Toggle "Add Map Link to Order Emails?" on Generals Settings page.
     */
    function toggleMapLinkOrderEmailOptions() {
      const addToEmail = $("#lpac_enable_delivery_map_link_in_email");

      if (!addToEmail) {
        return;
      }

      const addToEmailChecked = addToEmail.is(":checked");
      const linkType = $("#lpac_email_delivery_map_link_type");
      const linkLocation = $("#lpac_email_delivery_map_link_location");
      const selectedEmails = $("#lpac_email_delivery_map_emails");

      // Hide suboptions if feature disabled
      if (!addToEmailChecked) {
        linkType.closest("tr").hide();
        linkLocation.closest("tr").hide();
        selectedEmails.closest("tr").hide();
      }

      addToEmail.on("click", () => {
        if (addToEmail.is(":checked")) {
          linkType.closest("tr").show();
          linkLocation.closest("tr").show();
          selectedEmails.closest("tr").show();
        } else {
          linkType.closest("tr").hide();
          linkLocation.closest("tr").hide();
          selectedEmails.closest("tr").hide();
        }
      });
    }

    /**
     * Toggle "Enable Places Autocomplete Feature" on Generals Settings page.
     */
    function togglePlacesAutoCompleteOptions() {
      const placesAutoComplete = $("#lpac_enable_places_autocomplete");

      if (!placesAutoComplete) {
        return;
      }

      const placesAutoCompleteChecked = placesAutoComplete.is(":checked");
      const placesAllowedFields = $("#lpac_places_autocomplete_fields");
      const placesAutoCompleteHideMap = $("#lpac_places_autocomplete_hide_map");
      const placesAutoCompleteCountryRestrictions = $(
        "select[name^=lpac_places_autocomplete_country_restrictions]"
      );
      const placesAutoCompleteType = $("#lpac_places_autocomplete_type");

      if (!placesAutoCompleteChecked) {
        placesAllowedFields.closest("tr").hide();
        placesAutoCompleteHideMap.closest("tr").hide();
        placesAutoCompleteCountryRestrictions.closest("tr").hide();
        placesAutoCompleteType.closest("tr").hide();
      }

      placesAutoComplete.on("click", () => {
        if (placesAutoComplete.is(":checked")) {
          placesAllowedFields.closest("tr").show();
          placesAutoCompleteHideMap.closest("tr").show();
          placesAutoCompleteCountryRestrictions.closest("tr").show();
          placesAutoCompleteType.closest("tr").show();
        } else {
          placesAllowedFields.closest("tr").hide();
          placesAutoCompleteHideMap.closest("tr").hide();
          placesAutoCompleteCountryRestrictions.closest("tr").hide();
          placesAutoCompleteType.closest("tr").hide();
        }
      });
    }

    /**
     * Toggle force use of places autocomplete feature notice text field.
     */
    function toggleForceUsePlacesAutocompleteNoticeField() {
      const forceUse = $("#lpac_force_places_autocomplete");

      if (!forceUse.is(":checked")) {
        toggleField("#lpac_force_places_autocomplete_notice_text");
      }

      forceUse.on("click", () => {
        toggleField("#lpac_force_places_autocomplete_notice_text");
      });
    }

    /**
     * Set a dummy image for Shipping Regions feature.
     */
    function setPlottedOrdersDummyMapImage() {
      const queryString = window.location.search;
      const urlParams = new URLSearchParams(queryString);
      if (urlParams.get("section") !== "export") {
        return;
      }

      const mapDiv = $(".lpac-map");

      if (mapDiv && mapDiv.children().length == 0) {
        mapDiv.css(
          "background-image",
          `url( ${lpacAssetsFolderPath}img/plotted-orders-map-sample.png )`
        );
        mapDiv.css("background-size", "contain");
        mapDiv.css("background-repeat", "no-repeat");
        mapDiv.addClass("dummy-map");
      }
    }

    /**
     * Set a dummy image for Shipping Regions feature.
     */
    function setShippingRegionsDummyMapImage() {
      const queryString = window.location.search;
      const urlParams = new URLSearchParams(queryString);
      if (urlParams.get("section") !== "shipping") {
        return;
      }

      const mapDiv = $(".lpac-map");

      if (mapDiv && mapDiv.children().length == 0) {
        mapDiv.css(
          "background-image",
          `url( ${lpacAssetsFolderPath}img/shipping-regions-map-sample.png )`
        );
        mapDiv.css("background-size", "contain");
        mapDiv.css("background-repeat", "no-repeat");
        mapDiv.addClass("dummy-map");
      }
    }

    toggleForceUsePlacesAutocompleteNoticeField();
    toggleMapLinkOrderEmailOptions();
    togglePlacesAutoCompleteOptions();
    setPlottedOrdersDummyMapImage();
    setShippingRegionsDummyMapImage();
  });
})(jQuery);
