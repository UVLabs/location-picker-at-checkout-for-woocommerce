(function ($) {
	'use strict';

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
				
				$('#lpac-rules-saving-success').hide();
				$('#lpac-rules-saving-failed').hide();
				$('#lpac-rules-saving').show();
				const order = $(this).sortable('toArray', { attribute: 'data-id' });
			
				wp.ajax.post("lpac_map_visibility_rules_order", { rulesOrder: order })
					.done(function (response) {

						$('#lpac-rules-saving').hide();
						$('#lpac-rules-saving-failed').hide();
						$('#lpac-rules-saving-success').show().delay(1000).fadeOut('slow');;

						// console.log(response);

					})
					.fail(function (response) {

						$('#lpac-rules-saving').hide();
						$('#lpac-rules-saving-success').hide();
						$('#lpac-rules-saving-failed').show();

						// console.log(response);

					});


			}
		});

	});

})(jQuery);

