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
	// TODO Can most likely merge this into checkout-page-map.js
	$( document ).ready(

		function(){

			var map_div   = document.querySelector( '#lpac-map-container' );
			var map_shown = document.querySelector( '#lpac_is_map_shown' );
			var map_present = true;
			var map_visibility = '';

			if( typeof( map_shown ) === 'undefined' || map_shown === null ){
				console.log('LPAC: map_shown object not present, skipping...');
				return;
			}

			/**
			 * Detect if map is present and the display property
			 */
			if( typeof( map_div ) === 'undefined' || map_div === null ){
				map_present = false;
			}else{
				map_visibility = map_div.style.display;
			}

			if ( map_present === false || map_visibility === 'none' ) {

				if ( map_shown ) {
					map_shown.value = 0
				}

			} else {
				map_shown.value = 1
			}

		}
	);

})( jQuery );
