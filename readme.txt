=== Custom Field Suite ===
Contributors: logikal16, mgibbs189
Donate link: https://uproot.us/
Tags: custom fields, fields, forms, meta, postmeta, metabox, cck, wysiwyg, relationship, upload
Requires at least: 3.6
Tested up to: 3.8
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

= Getting Started =
[See the CFS overview page →](https://uproot.us/projects/cfs/)

= Documentation =
[View the documentation →](https://uproot.us/projects/cfs/documentation/)
[GitHub](https://github.com/mgibbs189/custom-field-suite)

= Support =
[Visit the support forums →](https://uproot.us/forums/)

== Installation ==

1. Download and activate the plugin.
2. Browse to the `Field Groups` menu to configure.

== Screenshots ==
1. A custom field group with field nesting (loop field)
2. Clicking on a field name expands the box to show options
3. Placement Rules determine where field groups appear
4. The Tools area for migrating field groups

== Changelog ==

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

= 1.9.9 =
* New checkerboard pattern for image thumbnails (props @jchristopher)
* New `cfs_get_input_fields` hook (props @dbisso)

= 1.9.8 =
* Replaced date picker
* Bugfix: loop field sorting issue (props @ChrisVanPatten)

= 1.9.7 =
* Minor code housekeeping

= 1.9.6 =
* Bugfix for certain fields disappearing (props Karin Suzakura)

= 1.9.5 =
* Load validation.js only when needed (props @voltronik)
* Show placement rules on field group listing (props @sc0ttkclark)
* Added field group `Order` and `Position` options
* Added Japanese translation (props Karin Suzakura)
* Fixed PHP strict notices

= 1.9.4 =
* Removed the need for session cookies
* Time support for datepicker (https://github.com/owenmead/Pikaday)
* Gravity Forms integration bugfix (props @joshlevinson)
* Better code commenting (PHPDoc)

= 1.9.3 =
* Fixed several Loop field order / sorting bugs (props @decamino)
* Bugfix: CFS respects wp_default_editor()
* Bugfix: (IE) removed extra comma from validation.js

= 1.9.2 =
* Replaced date picker with Pikaday
* Bugfix - javascript "undefined method replace" error
* Bugfix - incorrect plugins_url() for multi-site installs

= 1.9.1 =
* Added `cfs_pre_save_input` action hook
* Added `cfs_after_save_input` action hook
* Added Limit option to relationship fields
* Fixed ordering for `cfs_custom_validation` hook

= 1.9.0 =
* Table-based sessions for better compatibility
* Improved add-on page
* Added Persian translation (props Vahid Masoomi)
* Cleanup of API class

= 1.8.9 =
* Bugfix: compatibility fix for PHP sessions
* Bugfix: form save error with multiple edit pages open
* Added `submit_label` form parameter
* Updated translation file

= 1.8.8 =
* Several form() enhancements!
* Re-added loop "Row Display" option
* Ensuring that $cfs exists for template parts
* Added `cfs_pre_render_fields` filter for altering field settings

= 1.8.7 =
* Fixed newlines issue for sub-loop wysiwyg fields (props @jchristopher)
* Ability to use `get_reverse_related` on custom field types
* wysiwyg must be in "tinymce" mode for assets to load
* Added toggle button to loop fields

= 1.8.6 =
* Bugfix: CFS forced wysiwyg in "html" mode
* Bugfix: javascript error for file uploads without thumbnails

= 1.8.5 =
* Front-end forms
* 3.5 media uploader support (props @jchristopher)
* Added French translation (props @jcbrebion)
* Bugfix: PHP 5.3.x array_multisort warning
* Bugfix: WYSIWYG height within hidden container (e.g. loop)
* Bugfix: Error handling for missing field types
* Cleaned up admin-head.php

= 1.8.4.1 =
* Bugfix for `get_labels` (props @voltronik)

= 1.8.4 =
* Please backup your database!
* CFS requires WordPress 3.3 or above!
* Removed the `cfs_fields` table
* Performance improvements (caching tweaks)
* Replaced jQuery .live with .on (requires WP 3.3+)
* Refactored Import / Export (not compatible with old exports)
* Bugfix: prevent values from saving twice (with revisions enabled, WP runs `save_post` twice)

= 1.8.3 =
* Cleaned up `get_input_fields` API method
* Added support for dynamic Loop row labels (e.g. `{last_name}`)
* Removed unnecessary code

= 1.8.2 =
* Loop field UI tweaks
* Bugfix: rare PHP notice prevented Wysiwyg image insertion

= 1.8.1 =
* Fixed notice for Gravity Forms
* Cleaned up `get_field_info` API method
* Added Post Type Switcher plugin support

= 1.8.0 =
* New `get_field_info` API method
* New Add-ons system!
* Added a `Reset` option (under the Tools menu)
* Bugfix: error for relationships within nested loop fields (props felipepastor)
* Bugfix: $options not passed into API::get_fields (props @Gator92)
* Added pagination on CFS listing page (props codeSte)
* Removed uninstall hook (manual reset coming soon)
* Added `cfs_init` hook (props @Gator92)
* File field stability fixes

[See the full changelog](http://uproot.us/projects/cfs/changelog/)