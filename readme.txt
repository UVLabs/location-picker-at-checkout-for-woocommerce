=== Location Picker at Checkout for WooCommerce ===
Contributors: uriahs-victor
Donate link: https://uriahsvictor.com
Tags: woocommerce, location picker, map, geolocation, checkout map, delivery map, google map
Requires at least: 5.5
Requires PHP: 7.0
Tested up to: 5.8
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Let WooCommerce customers set their exact location for delivery on Google Maps at checkout.

== Description ==

Do you run a WooCommerce store where you need more detailed location information for a customer? If so, then why not let them choose their exact location on Google Maps? 

Location Picker At Checkout for WooCommerce (LPAC) allows store owners to add more flexibility to their store by letting their customers choose exactly where they'd like their product(s) to be delivered. This plugin is excellent for stores with delivery personnel that ship products to customers within a moderate geographical area. 

Location Picker At Checkout for WooCommerce enables store owners to get more precise location details without having to contact customers via other means for location information or directions. With this plugin, lots of time can be saved by allowing customers to select their exact location on Google Maps at checkout with WooCommerce.

### Billing & Shipping Address AutoFill For WooCommerce

This plugin has built-in support for automatically filling in the WooCommerce checkout fields with the information pulled from the Google map. Save users some typing while pulling accurate address information.

### Below are a few types of stores that would benefit tremendously from this plugin:

- Online Food Delivery websites
- Online Supermarkets
- Online Furniture websites
- Hardware Rental & Delivery websites
- Car Rental websites
- Pickup service websites
- Taxi and pickup scheduling websites
- And more

### Features:

- Allow customers to pick their exact location using Google Maps.
- Autofill checkout fields with information pulled from Google Maps.
- Automatically translates map's buttons based on site language (set in WordPress' general settings).
- Detect current location of customer.
- Include a QR Code or button link to the customer's selected location in the WooCommerce order emails.
- Customizable Map container
- "View on map" button to allow admin to view exact location for delivery of any order.
- Customers can see the delivery location they selected on past orders.
- Have a feature in mind? Feel free to submit it on the support forum.

### Configure Plugin:

Plugin settings are located in WordPress Admin Dashboard->WooCommerce->Location Picker At Checkout

== Installation ==


1. Extract the downloaded zip file and upload the `location-picker-at-checkout-for-woocommerce` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure the plugin in WooCommerce->Settings->Location Picker At Checkout

== Frequently Asked Questions ==

= The map doesn't show =

Ensure that you have setup the plugin with your API key by going to WordPress Dashboard->WooCommerce->Shipping->Location Picker At Checkout.

== Screenshots ==

1. Plugin Settings Dashboard
2. Checkout Page Map View (No location detected yet)
3. Checkout Page Map View (User selected their location)
4. Order Received 
5. View Order Map View (Past Order)
6. Map view of the customer delivery location on shop order page (in the WordPress dashboard)

== Changelog ==

= 1.2.0 =
* [New] Show or Hide map based on the shipping classes of items in the customer's cart.
* [Fix] Console error when the map was not enabled in Settings.
* [Improvement] Added coordinates fields to billing section of checkout page for better support of sites with custom checkout pages.
* [Improvement] More checks for better handling of sites with custom checkout pages.
* [Change] Minimum required PHP version is now 7.0. Please update your PHP version if you have not yet done so; contact your Web Host for assistance if needed.
* [Info] Added a new filter for "Detect Current Location" Button text: `lpac_find_location_btn_text`.
* [Info] Filter for map instruction text is: `lpac_map_instuctions_text`.
* [Info] Tested on WP 5.8.
* [Info] Tested on WC 5.6.

= 1.1.3 =
* [Improvement] Better handle sites with customized checkout pages.
* [Fix] Console error when the plugin map is not enabled.

= 1.1.2 =
* [New] Option added to allow admin to force customers to select their location on the map before being able to complete the order.
* [New] Customers can now move the map marker by clicking on the map. This was only possibly by dragging the map marker before.
* [New] A map view of the customer's location will now appear on the shop order page in WooCommerce->Orders->View.
* [Improvement] Refactored code that handles the checkout page map.
* [Change] Order emails multiselect option now uses selectWoo.
* [Info] Added links to support forum and plugin translation pages to the plugin settings page.

= 1.1.1 =
* [Fix] Plugin now works with customized checkout fields.
* [Info] Plugin can better parse and fill in State/County checkout fields

= 1.1.0 =
* [New] Option to add a button link or QR Code to the customer's map location in WooCommerce's emails.
* [New] Option to disable map on checkout and order details pages without disabling the plugin.
* [Fix] Customers were able to complete checkout without selecting a location on the map.
* [Change] Moved plugin settings to it's own tab in the WooCommerce Settings.
* [Info] Show admin notice if WooCommerce is not active or the site is not running HTTPS.
* [Info] Added admin notices if WooCommerce not active or site not running HTTPS.
* [Info] Tested on WC 5.4.

= 1.0.1 =
* [New] Automatically fill in address fields with information pulled from Google Maps.
* [Improvement] Error handling of invalid locations.
* [Improvement] Show users a message if they denied the website access to their location.
* [Improvement] Handle cases where customer is moving marker too quickly within a short period of time.
* [Improvement] Better handling of cases where geolocation feature is not available for the browser.

= 1.0.0 =
* Intial Release
