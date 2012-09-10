=== Custom Field Suite ===
Contributors: logikal16
Donate link: https://uproot.us/donate/
Tags: custom fields, custom, fields, cck, gravity forms, views, wysiwyg, relationship, date, loop, file upload
Requires at least: 3.2
Tested up to: 3.4.2
Stable tag: trunk

Visually create and manage custom fields.

== Description ==

Custom Field Suite is a custom fields plugin for WordPress. It allows you to visually create groups of custom fields. Each field group can be placed on the edit pages of your choosing. It also includes a lightweight API for displaying custom fields throughout your site.

CFS is a [fork](http://bit.ly/14vScj) of [Advanced Custom Fields](http://wordpress.org/extend/plugins/advanced-custom-fields/). The main goals of this plugin are stability, performance, and avoiding feature bloat.

= Why use CFS? =
* CFS is stable. Our top priority is keeping your data safe. We test all changes before releasing new versions.
* CFS is fast.
* CFS uses minimal resources.
* CFS supports [Gravity Forms](http://uproot.us/custom-field-suite/docs/gravity-forms-integration/).
* CFS supports custom field types (we had it before ACF).
* CFS is 100% free.
* [CFS is on GitHub!](https://github.com/logikal16/custom-field-suite/) You're encouraged to participate in the development process!

= Field Types =
* Text (api returns text)
* Textarea (api returns text with `<br />`)
* Wysiwyg Editor (api returns html)
* Date (api returns text)
* True / False (api returns 0 or 1)
* Select (api returns array of values)
* Relationship (api returns array of post IDs)
* User (api returns array of user IDs)
* File Upload (api returns file url or attachment ID)
* Loop (a container for other fields, api returns array of arrays)

= More Features =
* Drag-n-drop field management UI
* Loop fields support unlimited nesting!
* Placement Rules allow you to customize where each field group appears
* [Create your own field types](http://uproot.us/custom-field-suite/docs/custom-field-type/)
* Quickly migrate existing custom fields into CFS

= Documentation and Support =
* http://uproot.us/
* http://uproot.us/custom-field-suite/documentation/

== Installation ==

1. Download and activate the plugin.
2. Browse to `Field Groups` menu to configure.

== Screenshots ==
1. A custom field group with field nesting (loop field)
2. Clicking on a field name expands the box to show options
3. Placement Rules determine where field groups appear
4. The Tools area for migrating field groups

== Changelog ==

= 1.6.8 =
* Code cleanup
* Added new "pre_save_field" method
* Added "Page Template" placement rule
* Internal - converted select options from string to array

= 1.6.7 =
* Fixed file upload button bug (props baysaa)

= 1.6.6 =
* Added Polish translation (props Bartosz Arendt)
* Added loop option to expand rows by default
* Added field group option to hide the content editor
* Updated translation .POT file
* Fixed minor Gravity Forms integration bug

= 1.6.5 =
* Bugfix: Handle arrays for field get_option (props Migual Peixe)
* Added "cfs_matching_groups" hook to override which field groups are used (props Gator92)

= 1.6.4 =
* Added new wysiwyg "Formatting" option
* Enhancement: encapsulated jQuery UI CSS to prevent plugin conflicts

= 1.6.3 =
* New tabbed UI for Import / Export / Synchronize
* New "Debug Information" tab for helping with issue diagnosis
* Bugfix: checkbox missing from Select field multi-select option (props HighStand)
* Added new screenshots

= 1.6.2 =
* Bugfix: rare bug with deleting loop rows (props Lucia)
* Enhancement: new loop rows are expanded by default
* Enhancement: file field UI improvements
* Updated timepicker script (props @scottkclark)

= 1.6.1 =
* Field group import / export
* Cleaned PHP notices when WP_DEBUG = 1 (props @scottkclark)

= 1.6.0 =
* Drag-n-drop loop ordering on edit pages! (props Christian Bromann)
* Added PHP notice for sites using deprecated get_reverse_related signature

= 1.5.9 =
* Bugfix: get_reverse_related - ctype_digit returns FALSE for integer values between -128 and 255 (props Lucia)

= 1.5.8 =
* Enhancement: true_false field stores value, even if false
* Bugfix: API bugfix, enforce active field groups for get()
* Renamed Import page to Synchronize


= 1.5.7 =
* Enhancement: removed eval() in the API
* Enhancement: updated function signature for $cfs->get_reverse_related
* Bugfix: dashes in field name caused API error
* Bugfix: "Add Row" in loop field added 1 row per field group

= 1.5.6 =
* Bugfix: API ordering error when using multiple field groups on a single post
* Loop field support for custom "Add Row" label

= 1.5.5 =
* Bugfix: existing file upload fields should return URL instead of ID

= 1.5.4 =
* Bugfix: loop field default values not showing
* File upload: new "Return Value" option: File URL or Attachment ID
* API: get_reverse_related() has new 3rd parameter: $options
* API: get_reverse_related() supports post_type filtering

= 1.5.3 =
* Fixed UI issue with hierarchical Loop fields
* Updated chosen/select2 script
* Tested against WP 3.4

= 1.5.2 =
* Added new $field parameter to format_value functions
* Added textarea "Formatting" option (disable automatic BR tags)
* Fixed warnings when saving via Gravity Forms (props @flyingpylon)

= 1.5.1 =
* Bugfix: API returns array for last field's value (props @mickola, @brandon)
* Bugfix: issue with multiple loops and adding rows (props @stephen_d)

= 1.5.0 =
* BACK UP YOUR DATABASE before upgrading!
* Re-added loop field, complete rewrite
* Loop fields can now be nested within other loop fields (unlimited depth)
* New drag-n-drop field management interface
* Upgraded / optimized the date picker
* Fixed: saving multiple relationship values with $cfs->save()
* Fixed: saving multiple user values with $cfs->save()
* Updated screenshot
* Cleaned up CSS