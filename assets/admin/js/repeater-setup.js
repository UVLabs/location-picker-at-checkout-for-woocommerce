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

  $(document).ready(function () {
    $(".repeater").repeater({
      // (Optional)
      // start with an empty list of repeaters. Set your first (and only)
      // "data-repeater-item" with style="display:none;" and pass the
      // following configuration flag
      initEmpty: false,
      // (Optional)
      // "defaultValues" sets the values of added items.  The keys of
      // defaultValues refer to the value of the input's name attribute.
      // If a default value is not specified for an input, then it will
      // have its value cleared.
      defaultValues: {
        // 'text-input': 'foo'
      },
      // (Optional)
      // "show" is called just after an item is added.  The item is hidden
      // at this point.  If a show callback is not given the item will
      // have $(this).show() called on it.
      show: function () {
        $(this).slideDown();
      },
      // (Optional)
      // "hide" is called when a user clicks on a data-repeater-delete
      // element.  The item is still visible.  "hide" is passed a function
      // as its first argument which will properly remove the item.
      // "hide" allows for a confirmation step, to send a delete request
      // to the server, etc.  If a hide callback is not given the item
      // will be deleted.
      hide: function (deleteElement) {
        if (confirm("Are you sure you want to delete this entry?")) {
          $(this).slideUp(deleteElement);
        }
      },
      // (Optional)
      // You can use this if you need to manually re-index the list
      // for example if you are using a drag and drop library to reorder
      // list items.
      ready: function (setIndexes) {
        // $dragAndDrop.on('drop', setIndexes);
      },
      // (Optional)
      // Removes the delete button from the first list item,
      // defaults to false.
      isFirstItemUndeletable: true,
    });
  });
})(jQuery);
