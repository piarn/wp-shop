=== Shipping with Venipak for WooCommerce ===
Contributors: shopup
Tags: Venipak
Requires at least: 4.4
Tested up to: 6.7.1
Stable tag: 1.25.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Venipak delivery method plugin for WooCommerce. Delivery via courier and pickup points.

== Description ==

* Delivery to customer address.
* Delivery to Venipak Pickup points and lockers. Pickup map is displayed in checkout for user convenience.
* Collection of money by cash or card with COD service.
* In order to use the extension you must have an active contract with Venipak. https://www.venipak.com/
* Additionally, you must have user credentials for API of Venipak. Please contact Venipak sales. https://www.venipak.com/

support email: hello@akadrama.com

== Installation ==

1. Install the plugin
2. Configure with your venipak details. You must have user credentials for API of Venipak. Please contact Venipak sales. https://www.venipak.com/
3. Create venipak shipping methods

== Screenshots ==

== Changelog ==

= 1.25.1 =
* fix: undefined variable warning

= 1.25.0 =
* feat: Show delivery status in order list
* fix: deprecation warning

= 1.24.3 =
* fix: Checkout page css
* fix: Parcel size calculation improvements

= 1.24.2 =
* fix: Validation in case of not selected pickup point

= 1.24.1 =
* fix: Error of order edit page

= 1.24.0 =
* feat: Tracking
* fix: Validation priority fix

= 1.23.4 =
* Fix: Blocks api translations

= 1.23.3 =
* Fix: Blocks api fixes

= 1.23.2 =
* Feat: 6.7.1 wp support

= 1.23.0 =
* Feat: Blocks api support

= 1.22.6 =
* Fix: use billing address phone if phone is not available in shipping address

= 1.22.5 =
* Fix: improve shipping address validation

= 1.22.4 =
* Fix: pickup selection width
* Fix: security issue
* Fix: user billing address if shipping address is not available

= 1.22.3 =
* Fix: pickup selection validation error
* Fix: pickups loading fix

= 1.22.1 =
* Fix: if order_id is not set, get it from global scope

= 1.22.0 =
* Feature: Adding shortcut [venipak_tracking order_id="{order_id}"]

= 1.21.5 =
* Fix: Resolved an issue that caused errors during order processing when no shipping method was selected.

= 1.21.4 =
* Fix: auto package count

= 1.21.3 =
* Fix: use shipping company in label if defined

= 1.21.2 =
* Fix: pickup list loading 2

= 1.21.1 =
* Fix: pickup list loading

= 1.21.0 =
* Feature: predefined product-specific shipment counts
* Fix: use phone from shipping details
* Fix: fix deprecated php error


= 1.20.0 =
* Feature: HPOS support
* Feature: remember the last selected pickup point
* Fix: size restrictions for variations
* Fix: pickup list update

= 1.19.8 =
* Fix: Error log cleanup

= 1.19.7 =
* Fix: Sequence of labels

= 1.19.6 =
* Fix: Security vulnerability Cross Site Scripting (XSS)

= 1.19.5 =
* Fix: Set default products count for one label

= 1.19.4 =
* Fix: Load js and css only in cart or checkout pages

= 1.19.3 =
* Fix: Lockers list update period set to 1 day

= 1.19.2 =
* Fix: Locker weight conditions. It is possible now to create multiple locker shipping methods based on weight

= 1.19.1 =
* Fix: Multiple labels print order

= 1.19.0 =
* New Feature: Print multiple labels
* Fix: The courier method was not displayed because the minimum weight was not set
* Fix: Pickup selection aligment to the right

= 1.18.0 =
* New Feature: Return label service

= 1.17.13 =
* Fix: PHP warning

= 1.17.12 =
* Fix: Disable 30kg locker limit

= 1.17.11 =
* Fix: Pickup selector
* Fix: Shiping method title design
* Fix: Cod validation
