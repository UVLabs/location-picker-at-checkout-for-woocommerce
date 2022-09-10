(function ($) {
  "use strict";

  /**
   * All of the code for your public-facing JavaScript source
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

  /**
   * Output plugin version to console.
   */
  function outputLpacVersion() {
    console.log("LPAC version:", lpacVersion);
  }
  /**
   * Sets the selected store location for guests.
   */
  function shortcodeGuestStoreLocationSet() {
    const isLoggedIn = $("body").hasClass("logged-in");

    // This should only happen for guest users.
    if (isLoggedIn === true) {
      return;
    }

    const preferredOriginStore = localStorage.getItem(
      "lpac_user_preferred_store_location_id"
    );

    if (!preferredOriginStore.length > 0) {
      return;
    }

    $("#lpac-store-selector-shortcode select")
      .val(preferredOriginStore)
      .change();
  }

  /**
   * Listen to when the store location shortcode is changed.
   */
  function shortcodeStoreLocationChange() {
    const storeSelector = $("#lpac-store-selector-shortcode");

    if (!storeSelector.length > 0) {
      return;
    }

    storeSelector.change(function (e) {
      var selectedVal = $(this).find("option:selected").val();
      shorecodeSaveSelectedStoreLocation(selectedVal);
    });
  }

  /**
   * Save the selected store location id to our DB using ajax if the user is logged in.
   * If the user is a guest, then save it to localStorage
   */
  function shorecodeSaveSelectedStoreLocation(store_location_id) {
    const isLoggedIn = $("body").hasClass("logged-in");

    if (isLoggedIn === true) {
      wp.ajax
        .post("lpac_save_selected_store_location", { store_location_id })
        .done()
        .fail(function (response) {
          console.log(response);
        });
    } else {
      localStorage.setItem(
        "lpac_user_preferred_store_location_id",
        store_location_id
      );
    }
  }

  $(document).ready(function () {
    outputLpacVersion();
    shortcodeStoreLocationChange();
    shortcodeGuestStoreLocationSet();
  });
})(jQuery);
