=== Custom Field Suite ===
Contributors: logikal16
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=JMVGK3L35X6BU
Tags: custom, fields, custom fields, cck, post types, gravity forms, views, field permissions
Requires at least: 3.2
Tested up to: 3.4.1
Stable tag: trunk

Visually create and manage custom fields.

== Description ==

Visually create and manage custom fields. CFS is a fork of [Advanced Custom Fields](http://wordpress.org/extend/plugins/advanced-custom-fields/).

[Custom Field Suite is now on Github!](https://github.com/logikal16/custom-field-suite)

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
* Drag-n-drop field management UI that supports field hierarchy
* Customize where each field group will appear
* [Create your own field types](http://uproot.us/custom-field-suite/docs/custom-field-type/)
* [Gravity Forms integration](http://uproot.us/custom-field-suite/docs/gravity-forms-integration/)
* Quickly and easily migrate existing custom fields into CFS
* Loop fields can have unlimited nesting!

= Documentation and Support =
* http://uproot.us/
* http://uproot.us/forums/

== Installation ==

1. Download and activate the plugin.
2. Browse to `Field Groups` menu to configure.

== Screenshots ==
1. A custom field group, with loop nesting.

== Changelog ==

= 1.6.1 =
* Field group import / export
* Cleaned PHP notices when WP_DEBUG = 1

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