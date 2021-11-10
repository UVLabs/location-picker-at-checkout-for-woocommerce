=== Location Picker at Checkout for WooCommerce ===
Contributors: uriahs-victor
Donate link: https://uriahsvictor.com
Tags: woocommerce, location picker, map, geolocation, checkout map, delivery map, google map
Requires at least: 5.5
Requires PHP: 7.0
Tested up to: 5.8
Stable tag: 1.3.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Let WooCommerce customers set their exact location for delivery on Google Maps at checkout.

== Description ==

Do you run a WooCommerce store where you need more detailed location information from a customer? If so, then why not let them choose their exact location on Google Maps? 

Location Picker At Checkout for WooCommerce(LPAC) allows store owners to add more flexibility to their WooCommerce store by letting their customers choose exactly where they'd like their product(s) delivered. This plugin is excellent for stores with delivery personnel that ship products to customers within a moderate geographical area. It can also work for websites which offer Pickup services such as Private Taxi websites.

Location Picker At Checkout for WooCommerce(LPAC) enables store owners to get more precise location details without having to contact customers via other means for location information or directions. With this plugin, lots of time can be saved by allowing customers to select their exact location on Google Maps at checkout with WooCommerce.

## How Can Location Picker At Checkout for WooCommerce Help Me?

LPAC is a Checkout Location Picker plugin for WooCommerce that is suitable for any website that offers Delivery or Pickups for their customers. A Delivery website example would be an online restaurant, a Pickup website example would be a private taxi website. The plugin adds a Google map on the WooCommerce checkout page that customers can use to select their desired location. 

The plugin makes use of the Google Maps API to carry out it's functions; it can work as any of the following:

### WooCommerce Checkout Map Plugin 
This plugin adds a Google Map to the checkout page of WooCommerce which customers can use to select their location whether for deliveries or pickups.

### WooCommerce Billing & Shipping Address AutoFill Plugin

This plugin has built-in support for automatically filling in the WooCommerce checkout fields with the information pulled from the Google map. Save users some typing while pulling accurate address information.

### WooCommerce Restaurant & Food Delivery Plugin
This plugin is excellent for online restaurants or food delivery websites that deliver customer orders after they have been placed.

### WooCommerce Pickup Plugin
If you ran a website where customers select their location for pickup then Location Picker at Checkout for WooCommerce(LPAC) would be a suitable plugin for your website.

### Below are a few types of stores that would benefit tremendously from this plugin:

- Online Food Delivery websites
- Online Supermarkets
- Online Furniture websites
- Restaurants offering delivery via their website
- Hardware Rental & Delivery websites
- Car Rental websites
- Pickup service websites
- Taxi and pickup scheduling websites
- And more

### Features:

- Detect current location of customer
- Allow customers to pick their exact location using Google Maps
- Autofill checkout fields with information pulled from Google Maps
- Show/Hide Map based on Shipping Method
- Show/Hide Map based on Shipping Class
- Show Map based on Coupon Code
- Hide Map for Guest Checkout
- Include a QR Code or button link to the customer's selected location in the WooCommerce order emails.
- "View on map" button to allow admin to view exact location for delivery of any order.
- Customers can see the delivery location they selected on past orders.
- Customizable Map container
- Automatically translated map buttons based on the website's language (set in WordPress' general settings)
- Have a feature in mind? Feel free to submit it on the support forum.
- And More

### Support for:

- WooFunnels Funnel Builder Plugin for WordPress

### Configuring Plugin:

- The plugin settings are located in **WordPress Admin Dashboard->WooCommerce->Location Picker At Checkout** tab.

### Plugin Documentation

- You can find the plugin documentation [Here >>>](https://lpacwp.com/docs/)

### Feature Requests

- If you have any feature requests in mind then please submit them [Here >>>](https://lpacwp.com/contact/)

### Misc

- Learn more about the plugin and signup for the Pro waiting least [Here >>>](https://lpacwp.com)
- Assets [Attribution](https://lpacwp.com/attribution/)

This plugin is free software, and the most important features have been kept free and open to use so that all can benefit. If you like the plugin and believe that it's helped grow your business, then please consider [leaving a review](https://wordpress.org/support/plugin/map-location-picker-at-checkout-for-woocommerce/reviews/#new-post).

== Installation ==


1. Extract the downloaded zip file and upload the `location-picker-at-checkout-for-woocommerce` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin in WooCommerce->Settings->Location Picker At Checkout

Alternatively, install this plugin by searching for it from the plugins area of your WordPress website.

== Frequently Asked Questions ==

= The map doesn't show =

Ensure that you have setup the plugin with your API key by going to WordPress Dashboard->WooCommerce->Location Picker At Checkout. See [this doc](https://github.com/UVLabs/location-picker-at-checkout-for-woocommerce/wiki/Getting-Your-API-Key) for getting your API key. If you've entered your API key and the map still doesn't show, then please have a look at your  [browser console](https://balsamiq.com/support/faqs/browserconsole/#apple-safari) for any errors relating to the map, you can submit an issue thread on the plugin's [support forum](https://wordpress.org/support/plugin/map-location-picker-at-checkout-for-woocommerce/)

= Nothing happens when I click on the map =

These sorts of issues are usually due to a JavaScript issue on the website. Check your [browser console](https://balsamiq.com/support/faqs/browserconsole/#apple-safari) for any errors that might point to the cause. Feel free to post those errors in the support forum and include a full screenshot of your browser console.

= Map says "For development purposes only" =

This message shows when you have not finished setting up the map correctly inside the Google Cloud Console. Please make sure you've followed all the steps from the [setup guide](https://github.com/UVLabs/location-picker-at-checkout-for-woocommerce/wiki/Getting-Your-API-Key) including the "Setting up Billing & Google Monthly Credit" section.

= Do I need to pay to use this plugin? =

No! The plugin is free to use. The Google Maps APIs it uses, however, do require you to setup an account on Google's Cloud Console. The process is easy and requires you also attaching a billing method to your account. Google provides a $200 monthly credit for usage of their APIs so you don't have to worry about paying anything unless you receive alot of orders on your store (upwards of 15,000 orders in a month). See [this step](https://github.com/UVLabs/location-picker-at-checkout-for-woocommerce/wiki/Getting-Your-API-Key#setting-up-billing--google-monthly-credit) in the setup guide for more details about how this works.

= Why are the plugin settings not in my language? = 

If the plugin settings are not in your language then it means translations for your language do not currently exist for the plugin. You are more than welcome to help translate the plugin into your language [here](https://translate.wordpress.org/projects/wp-plugins/map-location-picker-at-checkout-for-woocommerce/)

= This plugin doesn't have all the features I want, what do I do? =

Post it on the [support forum](https://wordpress.org/support/plugin/map-location-picker-at-checkout-for-woocommerce/) of the plugin.

== Screenshots ==

1. Plugin Settings Dashboard
2. Map Visibility Rules Dashboard
3. Checkout Page Map View (Before customer clicks "Detect Current Location" button)
4. Checkout Page Map View (After customer clicks "Detect Current Location" button and selects their location)
5. Order Received Page with Map
6. View Order Page (Past Order) with Map 
7. Map view of the customer delivery location on shop order page (in the WordPress dashboard)
8. Delivery location button inside email (goes to customer selected location when clicked)
9. Delivery location QR Code inside email (goes to customer selected location when scanned)

== Upgrade Notice ==

= 1.3.3 =
The way in which shipping methods are saved by the plugin has been changed. Please go to WooCommerce->Location Picker at Checkout->Visibility Rules and set the desired shipping methods you'd like the map to be hidden for, then save your changes.

== Changelog ==

= 1.3.4 =
* [New] Get the customer's last order location and display it on the map at checkout. 
* [New] Option to remove Plus Code from Google Map addresses. 
* [Fix] Periods were being stripped from default coordinates input boxes. 
* [Fix] Wrong text domain for some text strings.
* [Fix] Blank infowindow was showing on order maps when shipping address was not present.
* [Info] Plugin has an [Official Website.](https://lpacwp.com) 

= 1.3.3 =
* [New] Added support for WooFunnels' custom checkout pages. 
* [New] Added a Map Visibility rules table to allow store owners to set the sequence they'd like rules to be ran.
* [New] Rule to show map based on coupon code.
* [New] Rule to hide map for guest orders.
* [Fix] Plugin would still try to run even though WooCommerce was inactive.
* [Improvement] Map display rules now use Ajax to determine whether or not to show the map.
* [Improvement] Added a new submenu tab called "Visibility Rules" which houses settings that control when the map is hidden/shown on the checkout page.
* [Info] Tested on WC 5.8.

= 1.3.2 =
* [Fix] Location button and QR code in emails were sometimes not centered.
* [Improvement] Added more support for older browsers.
* [Improvement] Show info window inside map on order-received, view-order, and dashboard view-order pages.
* [Dev] Two new filters for the delivery location button that appear in emails: `lpac_email_btn_p_styles` and `lpac_email_btn_a_styles`.

= 1.3.1 =
* [Fix] Missing class error.

= 1.3.0 =
* [Improvement] Code quality changes to plugin.

= 1.2.2 =
* [New] Added a new option in settings to hide the map based on the chosen shipping method.
* [Improvement] Better plugin settings arrangement.
* [Info] Tested on WC 5.7.

= 1.2.1 =
* [Improvement] Better assign coordinates input fields variables on checkout page.

= 1.2.0 =
* [New] Show or Hide map based on the shipping classes of items in the customer's cart.
* [Fix] Console error when the map was not enabled in Settings.
* [Improvement] Added coordinates fields to billing section of checkout page for better support of sites with custom checkout pages.
* [Improvement] More checks for better handling of sites with custom checkout pages.
* [Change] Minimum required PHP version is now 7.0. Please update your PHP version if you have not yet done so; contact your Web Host for assistance if needed.
* [Dev] Added a new filter for "Detect Current Location" Button text: `lpac_find_location_btn_text`.
* [Dev] Filter for map instruction text is: `lpac_map_instuctions_text`.
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
