=== Custom Field Suite ===
Contributors: logikal16
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=JMVGK3L35X6BU
Tags: custom, fields, custom fields, cck, post types, gravity forms, views, field permissions
Requires at least: 3.2
Tested up to: 3.4
Stable tag: trunk

Visually create and manage custom fields.

== Description ==

Visually create and manage custom fields. Custom Field Suite is a fork of [Advanced Custom Fields](http://wordpress.org/extend/plugins/advanced-custom-fields/).

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

= 1.5.7 =
* Enhancement: removed eval() in the API
* Enhancement: updated function signature for $cfs->get_reverse_related
* Bugfix: dashes in field name causes API error
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

= 1.4.7 =
* Added fork credits
* Removed loop field option for new fields

= 1.4.6 =
* Tons of bugfixes
* Loop fields begin with zero rows
* Added top-level menu (Field Groups)

= 1.4.5 =
* Proper cleanup on uninstall
* Fixes for multi-site admin URLs
* Fixed thickbox display for changelog

= 1.4.4 =
* Added new field type: User
* Added reverse lookup method: get_reverse_related

= 1.4.3 =
* Fixed a bunch of PHP notices (for those using WP_DEBUG)

= 1.4.2 =
* BACK UP YOUR DATABASE before upgrading!
* Improved how relationship fields save data
* Updated multiselect script (Chosen)
* Updated timepicker script

= 1.4.1 =
* Bugfix: wysiwyg field breaks if editor defaults to HTML tab

= 1.4.0 =
* Ability to select private posts in placement rules (props @jevets)

= 1.3.9 =
* Updated translation file
* Cleaned up PHP notices

= 1.3.8 =
* Bugfix: custom translation file path incorrect

= 1.3.7 =
* Bugfix: gravity form data not saving to correct post type

= 1.3.6 =
* Added thumbnail for uploaded images
* Bugfix: loop not displaying properly when saving first 2+ rows
* Bugfix: wysiwyg field not loading when adding dynamically within loop

= 1.3.5 =
* Bugfix: rare bug with relationship select boxes
* Bugfix: private posts now appear within Placement Rules
* Bugfix: prevent "navigate away from page" box on save
* Upload button appears as "Attach File" instead of "Insert into Post"

= 1.3.4 =
* Added custom field import / mapping script

= 1.3.3 =
* Upgraded chosen.js
* Added get_labels() API method
* Bugfix: Javascript issues for some fields within loop (wysiwyg, date, relationship)

= 1.3.2 =
* Bugfix: in some cases, the "User Roles" placement rule prevented values from displaying
* Bugfix: only published field groups should appear on edit pages

= 1.3.1 =
* Added private posts to relationship field

= 1.3.0 =
* Gravity Forms integration!
* Better error handling for the API save() method

== Upgrade Notice ==

= 1.4.2 =
BACK UP YOUR DATABASE BEFORE UPGRADING! Data migration is necessary to improve relationship fields.