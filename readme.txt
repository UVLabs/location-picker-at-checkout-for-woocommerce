=== Location Picker at Checkout for WooCommerce ===
Contributors: uriahs-victor
Donate link: https://uriahsvictor.com
Tags: woocommerce, location picker, map, geolocation, checkout map, delivery map, google map
Requires at least: 5.5
Requires PHP: 5.6
Tested up to: 5.7
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Let WooCommerce customers set their exact location for delivery on Google Maps at checkout.

== Description ==

Do you run a WooCommerce store where you need more detailed location information for a customer? If so, then why not let them choose their exact location on Google Maps? 

Location Picker At Checkout for WooCommerce (LPAC) allows store owners to add more flexibility to their store by letting their customers choose exactly where they'd like their product(s) to be delivered. This plugin is excellent for stores with delivery personnel that ship products to customers within a moderate geographical area. 

Location Picker At Checkout for WooCommerce enables store owners to get more precise location details without having to contact customers via other means for location information or directions. With this plugin, lots of time can be saved by allowing customers to select their exact location on Google Maps at checkout with WooCommerce.

### Address AutoFill For WooCommerce

This plugin has built-in support for automatically filling in the WooCommerce checkout fields with the information pulled from the Google map. Save users some typing while pulling accurate address information.

### Store types that would benefit tremendously from this plugin include:

- Online Food Delivery websites
- Online Supermarkets
- Online Furniture websites
- Hardware Rental & Delivery websites
- Car Rental websites
- Pickup service websites
- Taxi and pickup scheduling websites
- And more

### Features:

- Allow customers to pick their exact location on Google maps.
- Autofill checkout fields with information pulled from Google Maps.
- Automatically translates map's buttons based on site language (set in WordPress' general settings).
- Detect current location of customer.
- Customizable Map container
- "View on map" button to allow admin to view exact location for delivery of any order.
- Customers can see the delivery location they selected on past orders.
- Have a feature in mind? Feel free to submit it on the support forum.

### Configure Plugin:

Plugin settings are located in WordPress Admin Dashboard->WooCommerce->Shipping->Location Picker At Checkout

== Installation ==


1. Extract the downloaded zip file and upload the `location-picker-at-checkout-for-woocommerce` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure the plugin in WooCommerce->Settings->Location Picker

== Frequently Asked Questions ==

= Map doesn't show =

Ensure that you have setup the plugin with your API key by going to WordPress Dashboard->WooCommerce->Shipping->Location Picker At Checkout.

== Screenshots ==

1. Plugin Settings Dashboard
2. Checkout Page Map View (No location detected yet)
3. Checkout Page Map View (User selected their location)
4. Order Received 
5. View Order Map View (Past Order)

== Changelog ==

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
