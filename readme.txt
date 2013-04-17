=== Custom Field Suite ===
Contributors: logikal16, mgibbs189
Donate link: http://uproot.us/donate/
Tags: custom fields, fields, forms, meta, postmeta, metabox, cck, wysiwyg, relationship, upload
Requires at least: 3.3
Tested up to: 3.6
Stable tag: trunk
License: GPL2

Custom Field Suite (CFS) is a lightweight custom fields plugin for WordPress.

== Description ==

Custom Field Suite (CFS) lets you add custom fields to various edit screens. Each "field group" lives in its own meta box. Add as many field groups as you need!

= Features =
* [How to Use CFS](http://uproot.us/docs/how-to-use-cfs/)
* Many field types: [Text](http://uproot.us/docs/text/), [Textarea](http://uproot.us/docs/textarea/), [Visual Editor](http://uproot.us/docs/wysiwyg/), [Date](http://uproot.us/docs/date/), [Color](http://uproot.us/docs/color/), [Select](http://uproot.us/docs/select/), [File Upload](http://uproot.us/docs/file-upload/), [User](http://uproot.us/docs/user/), [Relationship](http://uproot.us/docs/relationship/), [Loop](http://uproot.us/docs/loop/) and you can also [create your own](http://uproot.us/docs/creating-custom-field-types/)!
* Loop fields are repeatable containers for other fields. For example, place a `File Upload` field into a `Loop` field to create a photo gallery!
* Each field group has a "Placement Rules" area, where you control which edit screens the field group should appear on
* CFS works well with [Gravity Forms](http://uproot.us/how-to-save-gravity-forms-data-into-custom-field-suite/), and can save Gravity Forms entries as post items.
* Drag-and-drop field management
* Field validation

**CFS is a fork of Advanced Custom Fields v2.** The main goals of CFS are stability, performance, and avoiding feature bloat.

= Why use Custom Field Suite? =
* CFS is super easy to use.
* CFS is stable. We thoroughly test our code before each release.
* Performance matters. CFS makes only a handful of database queries, and caching is used throughout.
* CFS is fast and uses few server resources.
* Custom fields can be searchable (using the default WordPress search) with the help of plugins like [Search Everything](http://wordpress.org/extend/plugins/search-everything/).

= Documentation and Support =
* http://uproot.us/
* http://uproot.us/projects/cfs/documentation/
* http://uproot.us/forums/

== Installation ==

1. Download and activate the plugin.
2. Browse to the `Field Groups` menu to configure.

== Screenshots ==
1. A custom field group with field nesting (loop field)
2. Clicking on a field name expands the box to show options
3. Placement Rules determine where field groups appear
4. The Tools area for migrating field groups

== Changelog ==

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

= 1.7.9 =
* Bugfix: field validation for new posts
* Improved `cfs_field_types` hook implementation
* Updated UI Timepicker to fix compability issues (props @saltcod)
* Allow for {empty} placeholder for empty select value
* Moved saving of field groups to the API class (props @Gator92)
* Updated links to uproot.us

= 1.7.8 =
* Field management - autofill and paste support (props @Gator92)
* Cleaned up PHP notices for get() with missing fields (props @jchristopher)
* Updated Hungarian translation (props [hutch.hu](http://hutch.hu/))

= 1.7.7 =
* Compatibility fix for WooFramework
* Bugfix: dragging wysiwyg fields within a loop field

= 1.7.6 =
* Autocomplete for "Posts" placement rule
* Allow for programmatic import/export (props unkhz)
* Removed deprecated parameter from `get_reverse_related`
* Disabled "Synchronize" feature, pending rewrite

= 1.7.5 =
* Updated German translations (props Sacha Brosi)
* Added hook: cfs_custom_validation
* Hovering over a relationship item will display its post type
* Updated select2 script (placement rules area)
* Refactored ajax handling

= 1.7.4 =
* Improved upgrade script
* Added German translation (props Sascha Brosi)
* Bugfix: javascript issues for fields within sub-loop fields (props @sc0ttkclark)
* Added tooltips to field management page

[See the full changelog](http://uproot.us/projects/cfs/changelog/)