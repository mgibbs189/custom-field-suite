=== Custom Field Suite ===
Contributors: mgibbs189
Donate link: http://customfieldsuite.com/
Tags: custom fields, fields, forms, meta, postmeta, metabox, cck, wysiwyg, relationship, repeater, upload
Requires at least: 3.8
Tested up to: 3.9.1
Stable tag: trunk
License: GPL2

Custom Field Suite (CFS) is a lightweight custom fields plugin

== Description ==

= Custom Field Suite (CFS) is a lightweight custom fields plugin =

* Visually create and manage custom fields
* Over a dozen field types: text, date, wysiwyg, relationship, file upload, user, loop, google maps, etc.
* Each field group has a "Placement Rules" area, where you define which edit screens to appear on
* Loop fields are repeatable containers for other fields. For example, place a `File Upload` field into a loop to create a gallery!
* Create your own field types using the `cfs_field_types` hook
* CFS works well with Gravity Forms, and can save GF entries as post items
* Includes client-side field validation
* This plugin is a free, lightweight alternative to Advanced Custom Fields.

= Important Links =
[Homepage →](http://customfieldsuite.com/)
[Documentation →](http://customfieldsuite.com/projects/cfs/documentation/)
[Github →](https://github.com/mgibbs189/custom-field-suite)

== Installation ==

1. Download and activate the plugin.
2. Browse to the `Field Groups` menu to configure.

== Screenshots ==
1. A custom field group with field nesting (loop field)
2. Clicking on a field name expands the box to show options
3. Placement Rules determine where field groups appear
4. The Tools area for migrating field groups

== Changelog ==

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