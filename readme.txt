=== Custom Field Suite ===
Contributors: mgibbs189
Donate link: http://customfieldsuite.com/
Tags: custom fields, fields, forms, meta, postmeta, metabox, wysiwyg, relationship, repeater, upload
Requires at least: 3.8
Tested up to: 4.0
Stable tag: trunk
License: GPLv2

Custom Field Suite (CFS) is a lightweight custom fields plugin

== Description ==

= Custom Field Suite (CFS) is a lightweight custom fields plugin =

* Visually create and manage custom fields
* Over a dozen field types: text, date, wysiwyg, relationship, file upload, user, loop, google maps, etc.
* Each field group has a "Placement Rules" area, where you define which edit screens to appear on
* Loop fields are repeatable containers for other fields. For example, place a `File Upload` field into a loop to create a gallery!
* Create your own field types using the `cfs_field_types` hook
* Includes client-side field validation
* This plugin is a free, lightweight alternative to Advanced Custom Fields.

= Important Links =
* [Homepage →](http://customfieldsuite.com/)
* [Documentation →](http://customfieldsuite.com/projects/cfs/documentation/)
* [Github →](https://github.com/mgibbs189/custom-field-suite)

= Translations =
* German (de_DE) - thanks to [David Decker](http://deckerweb.de/)
* Spanish (es_ES) - thanks to [Andrew Kurtis](http://www.webhostinghub.com/)
* Persian (fa_IR) - thanks to Vahid Masoomi
* French (fr_FR) - thanks to Jean-Christophe Brebion
* Hungarian (hu_HU)
* Italian (it_IT)
* Japanese (ja) - thanks to Karin Suzakura
* Polish (pl_PL) - thanks to [Digital Factory](digitalfactory.pl)
* Russian (ru_RU) - thanks to Glebcha

== Installation ==

1. Download and activate the plugin.
2. Browse to the `Field Groups` menu to configure.

== Screenshots ==
1. A custom field group with field nesting (loop field)
2. Clicking on a field name expands the box to show options
3. Placement Rules determine where field groups appear
4. The Tools area for migrating field groups

== Changelog ==

= 2.3.9 =
* Design refresh and cleanup (props @chrisvanpatten)
* New: user field min/max validation (props @chrisvanpatten)
* New: loop field min/max validation (props @christopherdro)
* Improved Add-ons screen (includes new "Synchronize" add-on)
* Replaced tooltip library
* Updated translations

= 2.3.8 =
* Fixed validation issue with WYSIWYG fields
* Increased toggle speed when viewing admin fields
* Cleanup of Tools and Add-ons pages
* Updated translations

= 2.3.7 =
* WYWISYG resize support
* Fixed WYSIWYG issue with all editors in text mode (props @sc0ttkclark)
* Fixed issue with partially-saved field groups

= 2.3.6 =
* Added Spanish translation (props Andrew Curtis)
* Updated translations
* Upgraded select2 to 3.5.1
* Hide Gravity Forms options when disabled
* Fixed label for "Post Formats"
* Fixed issue with Duplicate Posts plugin (props hissy)
* Added `cfs_disable_admin` to optionally hide group creation screens

= 2.3.5 =
* Fixed WYSIWYG "code" button from showing repeatedly

= 2.3.4 =
* Re-added WYSIWYG "code" button for WP 3.9+
* Date picker now highlights current day

= 2.3.3 =
* Added Hi-res select2 images
* Added Post Format placement rule (props @jchristopher)
* Fixed array_orderby method for PHP 5.3
* Updated translations

= 2.3.2 =
* Code refactoring
* Corrected Add-ons page with new URL
* Use `CFS()` instead of `$cfs` for future API usage
* Changed CFS->form init priority to 100 (for better compatibility)
* Fixed relationship fields not being scrollable (props @jchristopher)

= 2.3.1 =
* Refreshed field design (props @jchristopher)
* Removed deprecated WP 3.5 code (file upload field)
* Minor tweaks to the output of Exports

= 2.3.0 =
* Added Tab field type
* Updated translations

= 2.2.2 =
* Added Russian translation
* Added Italian translation
* Code cleanup

= 2.2.1 =
* Restored $cfs for template parts

= 2.2.0 =
* WP 3.9 compatibility
* Converted relationship fields to WP_Query (for more flexibility)
* Added `cfs_field_relationship_query_args` filter
* Updated translations
* Code cleanup

= 2.1.1 =
* Added animated scroll to validation errors (props @joshlevinson)
* Added `cfs_field_relationship_post_types` filter (props @jchristopher)
* Fixed PHP notice for loop sub-fields that don't exist
* Updated translations

= 2.1.0 =
* Replaced datepicker
* Added new `facetwp_field_relationship_post_types` filter

= 2.0.5 =
* Image CSS fix (props @voltronik)
* Force custom post type `query_var` to false
* Replaced true_false .live() method to .on()

= 2.0.4 =
* Fixed file upload issue with custom mime types

= 2.0.3 =
* WP 3.8 compatibility updates

= 2.0.2 =
* Fixed fatal error for relationship fields

= 2.0.1 =
* Validation fix for wysiwyg fields
* Replaced deprecated WPDB->escape method (props @joshlevinson)
* Fixed text fields to output '0'
* Better support for loop sub-field values
* Added new "depth" table column

= 2.0.0 =
* Improved i18n (props @deckerweb)
* Improved get_matching_groups API method (props @scottkclark)
* Support for required wysiwyg fields
* Support for the "Duplicate Posts" plugin
* Added ability to exclude fields from front-end forms
* Code formatting tweaks

[See the full changelog](http://uproot.us/projects/cfs/changelog/)