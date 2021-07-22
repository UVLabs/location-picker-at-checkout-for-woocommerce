(function( $ ) {
	'use strict';

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

	$( document ).ready(
		function(){

			var map_div   = document.querySelector( '.lpac-map' );
			var lat       = document.querySelector( '#lpac_latitude_field' );
			var long      = document.querySelector( '#lpac_longitude_field' );
			var map_shown = document.querySelector( '#lpac_is_map_shown' );

			if( typeof(map_shown) === 'undefined' || map_shown === null ){
				console.log('LPAC: map_shown object not present, skipping...')
				return;
			}

			if ( ! map_div ) {

				if ( lat && long ) {
					lat.remove();
					long.remove();
				}

				if ( map_shown ) {
					map_shown.value = 0
				}

			} else {
				map_shown.value = 1
			}

		}
	);

})( jQuery );
