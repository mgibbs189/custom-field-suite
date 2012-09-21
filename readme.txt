=== Custom Field Suite ===
Contributors: logikal16
Donate link: https://uproot.us/contributors/
Tags: fields, custom fields, cck, wysiwyg, relationship, date, loop, upload
Requires at least: 3.2
Tested up to: 3.5-alpha
Stable tag: trunk

Visually create and manage custom fields.

== Description ==

Custom Field Suite is a custom fields management plugin. It allows you to visually create groups of custom fields. Each field group can be placed on the edit pages of your choosing. It also includes a lightweight API for displaying custom fields throughout your site.

CFS is a [fork](http://bit.ly/14vScj) of Advanced Custom Fields. The main goals of this plugin are stability, performance, and avoiding feature bloat.

= Why use CFS? =
* CFS is stable. We test all changes before releasing a new version.
* CFS is fast.
* CFS uses minimal resources.
* CFS supports [Gravity Forms](https://uproot.us/how-to-save-gravity-forms-data-into-custom-field-suite/).
* CFS allows you to [add your own field types](http://uproot.us/custom-field-suite/docs/custom-field-type/).
* CFS is 100% free.
* [CFS is on GitHub!](https://github.com/logikal16/custom-field-suite/)

= Field Types =
* Text (api returns text)
* Textarea (api returns text with `<br />`)
* Wysiwyg Editor (api returns html)
* Date (api returns text)
* Color (api returns HEX value)
* True / False (api returns 0 or 1)
* Select (api returns array of values)
* Relationship (api returns array of post IDs)
* User (api returns array of user IDs)
* File Upload (api returns file url or attachment ID)
* Loop (a container for other fields, api returns array of arrays)

= More Features =
* Drag-n-drop field management UI
* Loop fields support unlimited nesting!
* Placement Rules let you choose where each field group appears
* Use the Sync feature to import existing custom field values

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

= 1.7.0 =
* Improved: field management UI
* Improved: select field returns associative array (value, label)
* Improved: rename postmeta keys when fields are renamed
* Improved: delete values when a field is deleted
* Bugfix: clear cache on $cfs->save (props dataworx)
* File field compatibility fixes for WP 3.5
* Added new logo (https://github.com/somerandomdude/Iconic)

= 1.6.9 =
* $cfs->save() returns the post ID (props Miguel Peixe)
* Added new "prepare_value" field method (format raw DB values)
* WPML 2.6+ support (properly copies custom field data on post duplication)
* Added new $options parameter to $cfs->get (documentation shortly)
* Fixed PHP notices (props @baxang)
* Bugfix: Page Template placement rule (props Hylkep)
* Bugfix: Error handling for field groups without fields
* Bugfix: Inability to remove all fields in a field group
* Improved: prevent consecutive underscores with field name generator

= 1.6.8 =
* Added "Page Template" placement rule
* Improved Loop field UI (props @tdwesten)
* Converted select options from string to array (internal)
* Added new "pre_save_field" method
* Code cleanup

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
* Improved: encapsulated jQuery UI CSS to prevent plugin conflicts

[See the full changelog](https://uproot.us/custom-field-suite/changelog/)
