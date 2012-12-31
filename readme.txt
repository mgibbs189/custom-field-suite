=== Custom Field Suite ===
Contributors: logikal16, mgibbs189
Donate link: http://uproot.us/donate/
Tags: custom fields, fields, postmeta, cck, wysiwyg, relationship, upload
Requires at least: 3.3
Tested up to: 3.5
Stable tag: trunk
License: GPL2

Visually add custom fields to your WordPress edit pages.

== Description ==

Custom Field Suite (CFS) lets you visually create and manage custom fields.

= Features =
* Many field types: [text](http://uproot.us/docs/text/), [textarea](http://uproot.us/docs/textarea/), [wysiwyg](http://uproot.us/docs/wysiwyg/), [date](http://uproot.us/docs/date/), [color](http://uproot.us/docs/color/), [select](http://uproot.us/docs/select/), [file upload](http://uproot.us/docs/file-upload/), [user](http://uproot.us/docs/user/), [relationship](http://uproot.us/docs/relationship/), and [loop](http://uproot.us/docs/loop/)
* Field validation
* Drag-and-drop field management
* Unlimited nesting for loop fields
* Easily customize where each field group appears

**CFS is a fork of Advanced Custom Fields.** The main goals of this plugin are stability, performance, and avoiding feature bloat.

= Why use Custom Field Suite? =
* CFS is super easy to use.
* CFS is stable. We test all changes before releasing a new version.
* CFS is fast and uses minimal server resources.
* CFS works well with [Gravity Forms](http://uproot.us/how-to-save-gravity-forms-data-into-custom-field-suite/) by saving Gravity Forms entries as post items.
* CFS supports [adding your own field types](http://uproot.us/docs/creating-custom-field-types/).
* [CFS is on GitHub!](https://github.com/mgibbs189/custom-field-suite/)

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